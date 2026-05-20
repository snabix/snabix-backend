<?php

declare(strict_types=1);

namespace App\CLI;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Filament\Commands\MakeUserCommand;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:make-admin', aliases: ['auth:make-admin'])]
class AuthCLIMakeAdminUser extends MakeUserCommand
{
    protected $name        = 'app:make-admin';

    /** @var array<string> */
    protected $aliases     = [
        'auth:make-admin',
    ];

    protected $description = 'Создать администратора';

    public function handle(): int
    {
        if ($this->option('panel') === null) {
            $this->input->setOption('panel', 'admin');
        }

        $exitCode = parent::handle();

        if ($exitCode === self::SUCCESS) {
            $this->assignSuperAdminRole();
        }

        return $exitCode;
    }

    /**
     * @return class-string<EloquentAdmin>
     */
    protected function getUserModel(): string
    {
        return EloquentAdmin::class;
    }

    private function assignSuperAdminRole(): void
    {
        $email = $this->option('email');

        if (! is_string($email) || trim($email) === '') {
            return;
        }

        $admin = EloquentAdmin::query()
            ->where('email', $email)
            ->first();

        if (! $admin instanceof EloquentAdmin) {
            return;
        }

        $role  = Role::findOrCreate('super_admin', 'admin');

        $admin->assignRole($role);
    }
}
