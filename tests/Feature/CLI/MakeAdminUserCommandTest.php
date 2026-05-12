<?php

declare(strict_types=1);

namespace Tests\Feature\CLI;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Illuminate\Support\Facades\Artisan;
use Tests\Feature\FeatureTestCase;

class MakeAdminUserCommandTest extends FeatureTestCase
{
    public function test_admin_user_can_be_created_via_project_command(): void
    {
        $exitCode = Artisan::call('app:make-admin', [
            '--name'     => 'admin',
            '--email'    => 'created-admin@admin.com',
            '--password' => '1',
        ]);

        $this->assertSame(0, $exitCode);

        $this->assertDatabaseHas('admins', [
            'email' => 'created-admin@admin.com',
            'name'  => 'admin',
        ]);

        $this->assertTrue(EloquentAdmin::query()->where('email', 'created-admin@admin.com')->exists());
    }
}
