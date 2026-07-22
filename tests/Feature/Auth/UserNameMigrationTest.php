<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Migrations\Migration;
use ReflectionMethod;
use Tests\Feature\FeatureTestCase;

class UserNameMigrationTest extends FeatureTestCase
{
    public function test_legacy_placeholder_pair_is_replaced_with_nulls(): void
    {
        $legacyUser = EloquentUser::factory()->create([
            'first_name' => 'User',
            'last_name'  => 'Account',
        ]);
        $namedUser  = EloquentUser::factory()->create([
            'first_name' => 'User',
            'last_name'  => 'Smith',
        ]);

        $migration  = require database_path(
            'migrations/2026_07_18_010000_remove_placeholder_user_names.php',
        );

        $this->assertInstanceOf(Migration::class, $migration);

        (new ReflectionMethod($migration, 'up'))->invoke($migration);

        $legacyUser->refresh();
        $namedUser->refresh();

        $this->assertNull($legacyUser->first_name);
        $this->assertNull($legacyUser->last_name);
        $this->assertSame('User', $namedUser->first_name);
        $this->assertSame('Smith', $namedUser->last_name);
    }
}
