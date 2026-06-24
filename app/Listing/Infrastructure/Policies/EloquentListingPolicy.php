<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Policies;

use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EloquentListingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentListing');
    }

    public function view(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('View:EloquentListing');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentListing');
    }

    public function update(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('Update:EloquentListing');
    }

    public function delete(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('Delete:EloquentListing');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentListing');
    }

    public function restore(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('Restore:EloquentListing');
    }

    public function forceDelete(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('ForceDelete:EloquentListing');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentListing');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentListing');
    }

    public function replicate(AuthUser $authUser, EloquentListing $eloquentListing): bool
    {
        return $authUser->can('Replicate:EloquentListing');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentListing');
    }
}
