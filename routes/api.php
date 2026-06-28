<?php

declare(strict_types=1);

use App\Auth\Http\ChangePassword\ChangePasswordController;
use App\Auth\Http\DeleteProfileAddress\DeleteProfileAddressController;
use App\Auth\Http\DeleteProfileAvatar\DeleteProfileAvatarController;
use App\Auth\Http\ForgotPassword\ForgotPasswordController;
use App\Auth\Http\ListActiveSessions\ListActiveSessionsController;
use App\Auth\Http\ListProfileAddresses\ListProfileAddressesController;
use App\Auth\Http\Logout\LogoutController;
use App\Auth\Http\ReplaceProfileAddresses\ReplaceProfileAddressesController;
use App\Auth\Http\ResendEmailVerification\ResendEmailVerificationController;
use App\Auth\Http\ResetPassword\ResetPasswordController;
use App\Auth\Http\ShowProfile\ProfileController;
use App\Auth\Http\SignIn\SignInController;
use App\Auth\Http\SignUp\SignUpController;
use App\Auth\Http\TerminateOtherSessions\TerminateOtherSessionsController;
use App\Auth\Http\TerminateSession\TerminateSessionController;
use App\Auth\Http\UpdateProfile\UpdateProfileController;
use App\Auth\Http\UpdateProfileAvatar\UpdateProfileAvatarController;
use App\Auth\Http\VerifyEmail\VerifyEmailController;
use App\Bot\Http\BotServiceController;
use App\Catalog\Http\GetCategoryAttributes\GetCategoryAttributesController;
use App\Catalog\Http\ListRootCategories\ListRootCategoriesController;
use App\Catalog\Http\ShowCategoryBranch\ShowCategoryBranchController;
use App\Listing\Http\AddListingFavorite\AddListingFavoriteController;
use App\Listing\Http\ArchiveListing\ArchiveListingController;
use App\Listing\Http\CreateListing\CreateListingController;
use App\Listing\Http\DeleteListing\DeleteListingController;
use App\Listing\Http\DeleteListingMedia\DeleteListingMediaController;
use App\Listing\Http\ListFavoriteListings\ListFavoriteListingsController;
use App\Listing\Http\ListListings\ListListingsController;
use App\Listing\Http\ListPublicListings\ListPublicListingsController;
use App\Listing\Http\RemoveListingFavorite\RemoveListingFavoriteController;
use App\Listing\Http\ReorderListingMedia\ReorderListingMediaController;
use App\Listing\Http\SetMainListingMedia\SetMainListingMediaController;
use App\Listing\Http\ShowListing\ShowListingController;
use App\Listing\Http\ShowPublicListing\ShowPublicListingController;
use App\Listing\Http\SubmitListingForReview\SubmitListingForReviewController;
use App\Listing\Http\UpdateListing\UpdateListingController;
use App\Listing\Http\UploadListingMedia\UploadListingMediaController;
use App\Location\Http\ListCities\ListCitiesController;
use App\Location\Http\ListRegions\ListRegionsController;
use App\News\Http\ListPublishedNewsPosts\ListPublishedNewsPostsController;
use App\News\Http\ShowPublishedNewsPost\ShowPublishedNewsPostController;
use App\Notification\Http\NotificationPreferencesController;
use App\Notification\Http\UserNotificationsController;
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
        Route::get('me/addresses', ListProfileAddressesController::class)
            ->middleware('auth:sanctum');
        Route::put('me/addresses', ReplaceProfileAddressesController::class)
            ->middleware('auth:sanctum');
        Route::delete('me/addresses/{addressId}', DeleteProfileAddressController::class)
            ->middleware('auth:sanctum');
        Route::post('change-password', ChangePasswordController::class)
            ->middleware('auth:sanctum');
        Route::get('sessions', ListActiveSessionsController::class)
            ->middleware('auth:sanctum');
        Route::delete('sessions', TerminateOtherSessionsController::class)
            ->middleware('auth:sanctum');
        Route::delete('sessions/{sessionId}', TerminateSessionController::class)
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

    Route::prefix('locations')->group(function () {
        Route::get('/regions', ListRegionsController::class);
        Route::get('/cities', ListCitiesController::class);
    });

    Route::prefix('news')->group(function () {
        Route::get('/', ListPublishedNewsPostsController::class);
        Route::get('/{slug}', ShowPublishedNewsPostController::class);
    });

    Route::get('public/listings', ListPublicListingsController::class);
    Route::get('public/listings/{listingId}', ShowPublicListingController::class);

    Route::prefix('listings')->middleware('auth:sanctum')->group(function () {
        Route::get('/', ListListingsController::class);
        Route::get('/favorites', ListFavoriteListingsController::class);
        Route::post('/', CreateListingController::class);
        Route::post('/{listingId}/archive', ArchiveListingController::class);
        Route::post('/{listingId}/submit-for-review', SubmitListingForReviewController::class);
        Route::post('/{listingId}/media', UploadListingMediaController::class);
        Route::patch('/{listingId}/media/reorder', ReorderListingMediaController::class);
        Route::patch('/{listingId}/media/{mediaId}/main', SetMainListingMediaController::class);
        Route::delete('/{listingId}/media/{mediaId}', DeleteListingMediaController::class);
        Route::post('/{listingId}/favorite', AddListingFavoriteController::class);
        Route::delete('/{listingId}/favorite', RemoveListingFavoriteController::class);
        Route::get('/{listingId}', ShowListingController::class);
        Route::patch('/{listingId}', UpdateListingController::class);
        Route::delete('/{listingId}', DeleteListingController::class);
    });

    Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [UserNotificationsController::class, 'index']);
        Route::patch('/read-all', [UserNotificationsController::class, 'markAllRead']);
        Route::delete('/', [UserNotificationsController::class, 'deleteAll']);
        Route::patch('/{notificationId}/read', [UserNotificationsController::class, 'markRead']);
        Route::delete('/{notificationId}', [UserNotificationsController::class, 'delete']);
        Route::get('/preferences', [NotificationPreferencesController::class, 'show']);
        Route::put('/preferences', [NotificationPreferencesController::class, 'update']);
        Route::delete('/preferences', [NotificationPreferencesController::class, 'reset']);
    });

    Route::prefix('service/bot')->middleware('bot.service')->group(function () {
        Route::get('/health', [BotServiceController::class, 'health']);
        Route::get('/me', [BotServiceController::class, 'me']);
        Route::get('/stats', [BotServiceController::class, 'stats']);
    });
});
