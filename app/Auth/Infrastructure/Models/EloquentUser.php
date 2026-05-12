<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Models;

use Database\Factories\EloquentUserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * Class EloquentUser
 *
 * @property string  $id
 * @property string  $first_name
 * @property string  $last_name
 * @property string  $email
 * @property boolean $is_active
 * @property string  $phone_number
 *
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $email_verified_at
 */
class EloquentUser extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<EloquentUserFactory> */
    use HasFactory;
    use Notifiable;

    public $incrementing = false;

    protected $table     = 'users';

    protected $keyType   = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable  = [
        'id',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden    = [
        'password',
        'remember_token',
    ];

    /**
     * @return Factory<EloquentUser>
     */
    protected static function newFactory(): Factory
    {
        return EloquentUserFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'is_active'         => 'boolean',
        ];
    }
}
