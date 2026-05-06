<?php

declare(strict_types=1);

use App\Auth\Http\EmailVerification\VerifyEmailController;
use App\Auth\Http\Logout\LogoutController;
use App\Auth\Http\Profile\ProfileController;
use App\Auth\Http\SignIn\SignInController;
use App\Auth\Http\SignUp\SignUpController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('sign-up', SignUpController::class);
        Route::post('sign-in', SignInController::class);
        Route::get('verify-email', VerifyEmailController::class)
            ->middleware('signed')
            ->name('verify-email');
        Route::get('me', ProfileController::class)
            ->middleware('auth:sanctum');
        Route::post('logout', LogoutController::class)
            ->middleware('auth:sanctum');
    });
});
