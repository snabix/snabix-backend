<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Pages;

use App\Auth\Filament\Resources\Admins\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected static ?string $title   = 'Создать администратора';

    protected function getRedirectUrl(): string
    {
        return AdminResource::getUrl('index');
    }
}
