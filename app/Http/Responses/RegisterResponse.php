<?php

namespace App\Http\Responses;

use App\Http\Resources\LoginResource;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;


        return new LoginResource([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
