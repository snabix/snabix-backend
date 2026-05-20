<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class EloquentAdmin extends Authenticatable implements FilamentUser
{
    use HasRoles;
    use Notifiable;

    protected $table             = 'admins';

    protected string $guard_name = 'admin';

    /** @var list<string> */
    protected $fillable          = [
        'name',
        'email',
        'password',
    ];

    /** @var list<string> */
    protected $hidden            = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
