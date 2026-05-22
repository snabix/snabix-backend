<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Policies;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ListingPolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $this->adminCan($user, 'ViewAny:EloquentListing');
    }

    public function view(
        Authenticatable $user,
        EloquentListing $listing,
    ): Response {
        if ($this->adminCan($user, 'View:EloquentListing')) {
            return Response::allow();
        }

        return $user instanceof EloquentUser && $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(Authenticatable $user): bool
    {
        return $this->adminCan($user, 'Create:EloquentListing');
    }

    public function update(
        Authenticatable $user,
        EloquentListing $listing,
    ): Response {
        if ($this->adminCan($user, 'Update:EloquentListing')) {
            return Response::allow();
        }

        return $user instanceof EloquentUser && $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function delete(
        Authenticatable $user,
        EloquentListing $listing,
    ): Response {
        if ($this->adminCan($user, 'Delete:EloquentListing')) {
            return Response::allow();
        }

        return $user instanceof EloquentUser && $this->ownsListing($user, $listing)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function deleteAny(Authenticatable $user): bool
    {
        return $this->adminCan($user, 'DeleteAny:EloquentListing');
    }

    public function submitForReview(
        EloquentUser $user,
        EloquentListing $listing,
    ): Response {
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

    private function adminCan(
        Authenticatable $user,
        string $permission,
    ): bool {
        return $user instanceof EloquentAdmin && $user->can($permission);
    }
}
