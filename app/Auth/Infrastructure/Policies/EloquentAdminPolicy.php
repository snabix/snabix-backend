<?php

namespace App\Auth\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentAdminPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentAdmin');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('View:EloquentAdmin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentAdmin');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('Update:EloquentAdmin');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('Delete:EloquentAdmin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentAdmin');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('Restore:EloquentAdmin');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDelete:EloquentAdmin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentAdmin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentAdmin');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('Replicate:EloquentAdmin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentAdmin');
    }

}