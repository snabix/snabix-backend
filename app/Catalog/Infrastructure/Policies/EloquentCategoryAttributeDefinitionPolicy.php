<?php

declare(strict_types=1);

namespace App\Catalog\Infrastructure\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Auth\Access\HandlesAuthorization;

class EloquentCategoryAttributeDefinitionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentCategoryAttributeDefinition');
    }

    public function view(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('View:EloquentCategoryAttributeDefinition');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentCategoryAttributeDefinition');
    }

    public function update(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('Update:EloquentCategoryAttributeDefinition');
    }

    public function delete(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('Delete:EloquentCategoryAttributeDefinition');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentCategoryAttributeDefinition');
    }

    public function restore(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('Restore:EloquentCategoryAttributeDefinition');
    }

    public function forceDelete(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('ForceDelete:EloquentCategoryAttributeDefinition');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentCategoryAttributeDefinition');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentCategoryAttributeDefinition');
    }

    public function replicate(AuthUser $authUser, EloquentCategoryAttributeDefinition $eloquentCategoryAttributeDefinition): bool
    {
        return $authUser->can('Replicate:EloquentCategoryAttributeDefinition');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentCategoryAttributeDefinition');
    }

}