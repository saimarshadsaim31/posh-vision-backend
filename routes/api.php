<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Storage\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'store']);
Route::post('/auth/register', [RegisterController::class, 'store']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/storage/file/get-s3-signed-url', [FileController::class, 'generateSignedUrl']);
});


