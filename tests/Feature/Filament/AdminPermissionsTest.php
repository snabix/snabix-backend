<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Media\Infrastructure\Models\EloquentMedia;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Tests\Feature\FeatureTestCase;

class AdminPermissionsTest extends FeatureTestCase
{
    public function test_super_admin_role_bypasses_filament_admin_permissions(): void
    {
        $admin = EloquentAdmin::query()->create([
            'name'     => 'Super Admin',
            'email'    => 'super-admin@example.com',
            'password' => 'password',
        ]);
        $role  = Role::findOrCreate('super_admin', 'admin');

        $admin->assignRole($role);

        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', EloquentMedia::class));
        $this->assertTrue(Gate::forUser($admin)->allows('Create:EloquentMedia'));
    }

    public function test_admin_without_permissions_cannot_manage_filament_resources(): void
    {
        $admin = EloquentAdmin::query()->create([
            'name'     => 'Limited Admin',
            'email'    => 'limited-admin@example.com',
            'password' => 'password',
        ]);

        $this->assertFalse(Gate::forUser($admin)->allows('viewAny', EloquentMedia::class));
        $this->assertFalse(Gate::forUser($admin)->allows('Create:EloquentMedia'));
    }
}
