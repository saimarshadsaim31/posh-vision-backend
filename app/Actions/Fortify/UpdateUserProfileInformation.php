<?php

namespace App\Actions\Fortify;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'phone_number' => ['string', 'max:255'],
            'country' => ['string', 'max:255'],
            'state' => ['string', 'max:255'],
            'city' => ['string', 'max:255'],
            'zip_code' => ['string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'image' => ['required', 'url'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required'],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            if($user->role === 'artist') {
                $collection = Collection::where('user_id', $user->id)->first();

                if($collection) {
                    $collection->update([
                        'title' => $input['title'],
                        'image' => $input['image'],
                        'description' => $input['description'],
                        'shopify_publication_status' => 'draft',
                    ]);
                } else {
                    Collection::create([
                        'user_id' => $user->id,
                        'title' => $input['title'],
                        'image' => $input['image'],
                        'description' => $input['description'],
                        'shopify_publication_status' => 'draft',
                    ]);
                }
            }

            $user->forceFill([
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'email' => $input['email'],
                'phone_number' => @$input['phone_number'],
            'address' => @$input['address'],
            'city' => @$input['city'],
            'state' => @$input['state'],
            'country' => @$input['country'],
            'zip_code' => @$input['zip_code'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            'phone_number' => @$input['phone_number'],
            'address' => @$input['address'],
            'city' => @$input['city'],
            'state' => @$input['state'],
            'country' => @$input['country'],
            'zip_code' => @$input['zip_code'],
        ])->save();

        if($user->role === 'artist') {
            $collection = Collection::where('user_id', $user->id)->first();

            if($collection) {
                $collection->update([
                    'title' => $input['title'],
                    'image' => $input['image'],
                    'description' => $input['description'],
                    'shopify_publication_status' => 'draft',
                ]);
            } else {
                Collection::create([
                    'user_id' => $user->id,
                    'title' => $input['title'],
                    'image' => $input['image'],
                    'description' => $input['description'],
                    'shopify_publication_status' => 'draft',
                ]);
            }
        }

        $user->sendEmailVerificationNotification();
    }
}
