<?php

namespace App\Actions\Fortify;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
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
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'image' => ['required', 'url'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required'],
        ])->validate();

        $user = User::create([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'role' => 'artist',
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'phone_number' => @$input['phone_number'],
            'address' => @$input['address'],
            'city' => @$input['city'],
            'state' => @$input['state'],
            'country' => @$input['country'],
            'zip_code' => @$input['zip_code'],
        ]);

        Collection::create([
            'user_id' => $user->id,
            'title' => $input['title'],
            'image' => $input['image'],
            'description' => $input['description'],
            'shopify_publication_status' => 'draft',
        ]);

        return $user;
    }
}
