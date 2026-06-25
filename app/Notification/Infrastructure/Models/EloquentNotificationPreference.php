<?php

declare(strict_types=1);

namespace App\Notification\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;

class EloquentNotificationPreference extends Model
{
    protected $table    = 'notification_preferences';

    protected $fillable = ['user_id', 'event_key', 'site_enabled', 'email_enabled'];

    protected function casts(): array
    {
        return [
            'site_enabled'  => 'boolean',
            'email_enabled' => 'boolean',
        ];
    }
}
