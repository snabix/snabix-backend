<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Pages;

use App\Auth\Filament\Resources\Admins\AdminResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected static ?string $title = 'Редактирование администратора';

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
