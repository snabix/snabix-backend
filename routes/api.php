<?php

declare(strict_types=1);

use App\Auth\Http\EmailVerification\VerifyEmailController;
use App\Auth\Http\ForgotPassword\ForgotPasswordController;
use App\Auth\Http\Logout\LogoutController;
use App\Auth\Http\Profile\DeleteProfileAvatarController;
use App\Auth\Http\Profile\ProfileController;
use App\Auth\Http\Profile\UpdateProfileAvatarController;
use App\Auth\Http\Profile\UpdateProfileController;
use App\Auth\Http\ResetPassword\ResetPasswordController;
use App\Auth\Http\SignIn\SignInController;
use App\Auth\Http\SignUp\SignUpController;
use App\Catalog\Http\Categories\ListRootCategoriesController;
use App\Catalog\Http\Categories\ShowCategoryBranchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('sign-up', SignUpController::class);
        Route::post('sign-in', SignInController::class);
        Route::post('forgot-password', ForgotPasswordController::class);
        Route::post('reset-password', ResetPasswordController::class);
        Route::get('verify-email', VerifyEmailController::class)
            ->middleware('signed')
            ->name('verify-email');
        Route::get('me', ProfileController::class)
            ->middleware('auth:sanctum');
        Route::patch('me', UpdateProfileController::class)
            ->middleware('auth:sanctum');
        Route::post('me/avatar', UpdateProfileAvatarController::class)
            ->middleware('auth:sanctum');
        Route::delete('me/avatar', DeleteProfileAvatarController::class)
            ->middleware('auth:sanctum');
        Route::post('logout', LogoutController::class)
            ->middleware('auth:sanctum');
    });
    Route::prefix('categories')->group(function () {
        Route::get('/list', ListRootCategoriesController::class);
        Route::get('/{categoryId}/branch', ShowCategoryBranchController::class);
    });
});
