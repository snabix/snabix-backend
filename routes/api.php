<?php

declare(strict_types=1);

use App\Auth\Http\EmailVerification\ResendEmailVerificationController;
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
use App\Catalog\Http\CreateCategoryAttributeDefinition\CreateCategoryAttributeDefinitionController;
use App\Catalog\Http\DeleteCategoryAttributeDefinition\DeleteCategoryAttributeDefinitionController;
use App\Catalog\Http\GetCategoryAttributes\GetCategoryAttributesController;
use App\Catalog\Http\ListCategoryAttributeDefinitions\ListCategoryAttributeDefinitionsController;
use App\Catalog\Http\ListRootCategories\ListRootCategoriesController;
use App\Catalog\Http\ShowCategoryAttributeDefinition\ShowCategoryAttributeDefinitionController;
use App\Catalog\Http\ShowCategoryBranch\ShowCategoryBranchController;
use App\Catalog\Http\UpdateCategoryAttributeDefinition\UpdateCategoryAttributeDefinitionController;
use App\Listing\Http\CreateListing\CreateListingController;
use App\Listing\Http\DeleteListing\DeleteListingController;
use App\Listing\Http\ListListings\ListListingsController;
use App\Listing\Http\ListPublicListings\ListPublicListingsController;
use App\Listing\Http\ShowListing\ShowListingController;
use App\Listing\Http\UpdateListing\UpdateListingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('sign-up', SignUpController::class)
            ->middleware('throttle:auth.sign-up');
        Route::post('sign-in', SignInController::class)
            ->middleware('throttle:auth.sign-in');
        Route::post('forgot-password', ForgotPasswordController::class)
            ->middleware('throttle:auth.forgot-password');
        Route::post('reset-password', ResetPasswordController::class)
            ->middleware('throttle:auth.reset-password');
        Route::post('verify-email', VerifyEmailController::class)
            ->middleware(['auth:sanctum', 'throttle:auth.verify-email']);
        Route::post('email-verification-notification', ResendEmailVerificationController::class)
            ->middleware(['auth:sanctum', 'throttle:auth.resend-verification']);
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
        Route::get('/{categoryId}/attributes', GetCategoryAttributesController::class);
    });
    Route::get('public/listings', ListPublicListingsController::class);
    Route::prefix('admin/category-attribute-definitions')->middleware('auth:admin')->group(function () {
        Route::get('/', ListCategoryAttributeDefinitionsController::class);
        Route::post('/', CreateCategoryAttributeDefinitionController::class);
        Route::get('/{attributeDefinitionId}', ShowCategoryAttributeDefinitionController::class);
        Route::patch('/{attributeDefinitionId}', UpdateCategoryAttributeDefinitionController::class);
        Route::delete('/{attributeDefinitionId}', DeleteCategoryAttributeDefinitionController::class);
    });
    Route::prefix('listings')->middleware('auth:sanctum')->group(function () {
        Route::get('/', ListListingsController::class);
        Route::post('/', CreateListingController::class);
        Route::get('/{listingId}', ShowListingController::class);
        Route::patch('/{listingId}', UpdateListingController::class);
        Route::delete('/{listingId}', DeleteListingController::class);
    });
});
