<?php

declare(strict_types=1);

namespace App\Location\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Location\Infrastructure\Models\EloquentCity;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentCityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentCity');
    }

    public function view(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('View:EloquentCity');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentCity');
    }

    public function update(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('Update:EloquentCity');
    }

    public function delete(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('Delete:EloquentCity');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentCity');
    }

    public function restore(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('Restore:EloquentCity');
    }

    public function forceDelete(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('ForceDelete:EloquentCity');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentCity');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentCity');
    }

    public function replicate(AuthUser $authUser, EloquentCity $eloquentCity): bool
    {
        return $authUser->can('Replicate:EloquentCity');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentCity');
    }

}