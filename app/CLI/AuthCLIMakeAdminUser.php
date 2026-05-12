<?php

declare(strict_types=1);

namespace App\CLI;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Filament\Commands\MakeUserCommand;
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

        return parent::handle();
    }

    /**
     * @return class-string<EloquentAdmin>
     */
    protected function getUserModel(): string
    {
        return EloquentAdmin::class;
    }
}
