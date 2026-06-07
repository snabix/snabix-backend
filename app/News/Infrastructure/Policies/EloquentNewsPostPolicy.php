<?php

declare(strict_types=1);

namespace App\News\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentNewsPostPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentNewsPost');
    }

    public function view(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('View:EloquentNewsPost');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentNewsPost');
    }

    public function update(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('Update:EloquentNewsPost');
    }

    public function delete(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('Delete:EloquentNewsPost');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentNewsPost');
    }

    public function restore(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('Restore:EloquentNewsPost');
    }

    public function forceDelete(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('ForceDelete:EloquentNewsPost');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentNewsPost');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentNewsPost');
    }

    public function replicate(AuthUser $authUser, EloquentNewsPost $eloquentNewsPost): bool
    {
        return $authUser->can('Replicate:EloquentNewsPost');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentNewsPost');
    }

}