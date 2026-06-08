<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Policies;

use App\Location\Infrastructure\Models\EloquentRegion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EloquentRegionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentRegion');
    }

    public function view(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('View:EloquentRegion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentRegion');
    }

    public function update(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('Update:EloquentRegion');
    }

    public function delete(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('Delete:EloquentRegion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentRegion');
    }

    public function restore(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('Restore:EloquentRegion');
    }

    public function forceDelete(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('ForceDelete:EloquentRegion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentRegion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentRegion');
    }

    public function replicate(AuthUser $authUser, EloquentRegion $eloquentRegion): bool
    {
        return $authUser->can('Replicate:EloquentRegion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentRegion');
    }
}
