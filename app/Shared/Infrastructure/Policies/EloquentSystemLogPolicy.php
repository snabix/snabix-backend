<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Shared\Infrastructure\Models\EloquentSystemLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentSystemLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentSystemLog');
    }

    public function view(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('View:EloquentSystemLog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentSystemLog');
    }

    public function update(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('Update:EloquentSystemLog');
    }

    public function delete(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('Delete:EloquentSystemLog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentSystemLog');
    }

    public function restore(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('Restore:EloquentSystemLog');
    }

    public function forceDelete(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('ForceDelete:EloquentSystemLog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentSystemLog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentSystemLog');
    }

    public function replicate(AuthUser $authUser, EloquentSystemLog $eloquentSystemLog): bool
    {
        return $authUser->can('Replicate:EloquentSystemLog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentSystemLog');
    }

}