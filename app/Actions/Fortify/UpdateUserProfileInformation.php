<?php

namespace App\Actions\Fortify;

use App\Models\Collection;
use App\Models\User;
use App\ShopifyAdminApi;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update(User $user, array $input)
    {
        if ($user->role === 'artist') {
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
        } else {
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
            ])->validateWithBag('updateProfileInformation');
        }
        $collection = Collection::where('user_id', $user->id)->first();
            if($collection->shopify_collection_id !== null && $collection->shopify_collection_id !== "" && $user->role === "artist") {
                $shopifyResponse = ShopifyAdminApi::updateCollection([
                    "title" => $collection->title,
                    "description" => $collection->description,
                    "image" => $collection->image
                ],$collection->shopify_collection_id);
                if ($shopifyResponse->status === "error") {
                    return new JsonResponse([
                        "message" => "An error occurred while changing collection status",
                        "description" => $shopifyResponse->message
                    ], 400);
                }
            }
        $verified = $user;
        if ($input['email'] !== $user->email &&
            $verified instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            if($user->role === 'artist') {

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
