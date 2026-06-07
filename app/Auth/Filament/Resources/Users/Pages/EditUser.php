<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Pages;

use App\Auth\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $title   = 'Редактирования пользователя';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Просмотр'),
            DeleteAction::make()
                ->label('Удалить'),
        ];
    }
}
