<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EloquentUserPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentUser');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:EloquentUser');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentUser');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:EloquentUser');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:EloquentUser');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentUser');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:EloquentUser');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:EloquentUser');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentUser');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentUser');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:EloquentUser');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentUser');
    }
}
