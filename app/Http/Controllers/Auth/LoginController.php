<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoginResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Requests\LoginRequest;

class LoginController extends AuthenticatedSessionController
{
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            $user = Auth::user();
            if ($user->blocked === 1) {
                return new JsonResponse([
                    "message" => "Your account is blocked by admin.",
                ], 200);
            }
            $token = $user->createToken('auth_token', ['role:' . $user->role])->plainTextToken;

            return new LoginResource([
                'token' => $token,
                'user' => $user,
            ]);
        });
    }
}
