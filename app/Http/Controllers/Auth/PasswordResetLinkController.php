<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Str;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController as ControllersPasswordResetLinkController;

class PasswordResetLinkController extends ControllersPasswordResetLinkController
{
/**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Responsable
     */
    public function store(Request $request): Responsable
    {
        $request->validate([
            Fortify::email() => 'required|email',
            'reset_url' => 'required|url',
        ]);

        if (config('fortify.lowercase_usernames') && $request->has(Fortify::email())) {
            $request->merge([
                Fortify::email() => Str::lower($request->{Fortify::email()}),
            ]);
        }

        // Get the user
        $user = \App\Models\User::where(Fortify::email(), $request->{Fortify::email()})->first();

        if (!$user) {
            return app(\Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse::class, [
                'status' => Password::INVALID_USER,
            ]);
        }

        // Generate token
        $token = Password::getRepository()->create($user);

        $host = rtrim($request->input('reset_url'), '/'); // Ensure no trailing slash
        $resetLink = "{$host}?token={$token}&email=" . urlencode($user->{Fortify::email()});

        // Send email
        Mail::to($user->{Fortify::email()})->send(new \App\Mail\ResetPassword($resetLink));

        return app(\Laravel\Fortify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse::class, [
            'status' => Password::RESET_LINK_SENT,
        ]);
    }
}
