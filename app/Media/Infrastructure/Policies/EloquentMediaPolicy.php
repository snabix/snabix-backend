<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentMediaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentMedia');
    }

    public function view(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('View:EloquentMedia');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentMedia');
    }

    public function update(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('Update:EloquentMedia');
    }

    public function delete(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('Delete:EloquentMedia');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentMedia');
    }

    public function restore(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('Restore:EloquentMedia');
    }

    public function forceDelete(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('ForceDelete:EloquentMedia');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentMedia');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentMedia');
    }

    public function replicate(AuthUser $authUser, EloquentMedia $eloquentMedia): bool
    {
        return $authUser->can('Replicate:EloquentMedia');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentMedia');
    }

}