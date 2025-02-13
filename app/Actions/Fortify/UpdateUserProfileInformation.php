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
                $collection = Collection::find($user->id);

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
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
