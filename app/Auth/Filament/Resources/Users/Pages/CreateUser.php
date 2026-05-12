<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Pages;

use App\Auth\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title   = 'Создать пользователя';

    protected function getRedirectUrl(): string
    {
        return UserResource::getUrl('index');
    }
}
