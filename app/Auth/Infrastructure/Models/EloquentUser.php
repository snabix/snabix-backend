<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Models;

use App\Media\Infrastructure\Models\EloquentMedia;
use Database\Factories\EloquentUserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
class EloquentUser extends Authenticatable implements HasMedia
{
    use HasApiTokens;

    /** @use HasFactory<EloquentUserFactory> */
    use HasFactory;

    use InteractsWithMedia;
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
     * @return HasMany<EloquentUserAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(EloquentUserAddress::class, 'user_id')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    /**
     * @return MorphOne<EloquentMedia, $this>
     */
    public function avatarMedia(): MorphOne
    {
        return $this
            ->morphOne(EloquentMedia::class, 'model')
            ->where('collection_name', 'avatar')
            ->latestOfMany();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif']);
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
