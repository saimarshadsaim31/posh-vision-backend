<?php

namespace App\Http\Controllers\Auth;

use App\Http\Resources\LoginResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Responses\ProfileInformationUpdatedResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Controllers\ProfileInformationController as ControllersProfileInformationController;

class ProfileInformationController extends ControllersProfileInformationController
{
     /**
     * Update the user's profile information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Fortify\Contracts\UpdatesUserProfileInformation  $updater
     * @return \Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse
     */
    public function update(Request $request,
                           UpdatesUserProfileInformation $updater)
    {
        if (config('fortify.lowercase_usernames') && $request->has(Fortify::username())) {
            $request->merge([
                Fortify::username() => Str::lower($request->{Fortify::username()}),
            ]);
        }

        $updater->update($request->user(), $request->all());

        return app(ProfileInformationUpdatedResponse::class);
    }
    public function show(Request $request)
    {
        $user = Auth::user();
        $token = $request->bearerToken();
        return new LoginResource([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
