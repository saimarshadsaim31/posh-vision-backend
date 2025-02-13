<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\ProfileInformationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Storage\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'store']);
Route::post('/auth/register', [RegisterController::class, 'store']);
Route::post('/auth/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/auth/reset-password', [NewPasswordController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/storage/file/get-s3-signed-url', [FileController::class, 'generateSignedUrl']);

    Route::put('/auth/user/password', [PasswordController::class, 'update']);
    Route::put('/auth/user/profile-information', [ProfileInformationController::class, 'update']);
    Route::get('/auth/user/profile-information', [ProfileInformationController::class, 'show']);
});


