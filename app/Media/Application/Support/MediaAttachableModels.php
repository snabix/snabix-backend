<?php

declare(strict_types=1);

namespace App\Media\Application\Support;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\Auth\Infrastructure\Models\EloquentUser;
use Illuminate\Database\Eloquent\Model;

class MediaAttachableModels
{
    /**
     * @return array<class-string<Model>, string>
     */
    public static function options(): array
    {
        return [
            EloquentUser::class => 'Пользователи',
            EloquentAdmin::class => 'Администраторы',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function recordOptions(?string $modelClass): array
    {
        if (! is_string($modelClass) || ! array_key_exists($modelClass, self::options())) {
            return [];
        }

        if ($modelClass === EloquentUser::class) {
            return EloquentUser::query()
                ->orderBy('email')
                ->limit(100)
                ->get(['id', 'first_name', 'last_name', 'email'])
                ->mapWithKeys(
                    fn(EloquentUser $user): array => [
                        $user->id => trim($user->first_name . ' ' . $user->last_name) . ' · ' . $user->email,
                    ],
                )
                ->all();
        }

        if ($modelClass === EloquentAdmin::class) {
            return EloquentAdmin::query()
                ->orderBy('email')
                ->limit(100)
                ->pluck('email', 'id')
                ->mapWithKeys(fn(string $email, int $id): array => [(string) $id => $email])
                ->all();
        }

        return [];
    }
}
