<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentCategory');
    }

    public function view(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('View:EloquentCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentCategory');
    }

    public function update(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('Update:EloquentCategory');
    }

    public function delete(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('Delete:EloquentCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentCategory');
    }

    public function restore(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('Restore:EloquentCategory');
    }

    public function forceDelete(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('ForceDelete:EloquentCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentCategory');
    }

    public function replicate(AuthUser $authUser, EloquentCategory $eloquentCategory): bool
    {
        return $authUser->can('Replicate:EloquentCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentCategory');
    }

}