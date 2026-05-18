<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Policies;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Auth\Access\Response;

class ListingPolicy
{
    public function view(
        EloquentUser $user,
        EloquentListing $listing
    ): Response
    {
        return $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function update(
        EloquentUser $user,
        EloquentListing $listing
    ): Response
    {
        return $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(
        EloquentUser $user,
        EloquentListing $listing
    ): Response
    {
        return $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function submitForReview(
        EloquentUser $user,
        EloquentListing $listing
    ): Response
    {
        return $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    private function ownsListing(
        EloquentUser $user,
        EloquentListing $listing,
    ): bool {
        return $listing->user_id === $user->id;
    }
}
