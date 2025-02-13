<?php

namespace App\Http\Controllers\Auth;

use App\Http\Responses\PasswordUpdateResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\PasswordController as ControllersPasswordController;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Events\PasswordUpdatedViaController;

class PasswordController extends ControllersPasswordController
{
    public function update(Request $request, UpdatesUserPasswords $updater)
    {
        $updater->update($request->user(), $request->all());

        event(new PasswordUpdatedViaController($request->user()));

        return app(PasswordUpdateResponse::class);
    }
}
