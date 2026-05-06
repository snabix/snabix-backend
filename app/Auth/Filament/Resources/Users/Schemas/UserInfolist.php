<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Users\Schemas;

use App\Auth\Infrastructure\Models\EloquentUser;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Профиль')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Имя'),

                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),

                        IconEntry::make('email_verified_at')
                            ->label('Почта подтверждена')
                            ->boolean()
                            ->state(fn(EloquentUser $record): bool => $record->email_verified_at !== null),

                        TextEntry::make('email_verified_at')
                            ->label('Подтверждено в')
                            ->placeholder('Ещё не подтверждено')
                            ->dateTime('d.m.Y H:i'),
                    ]),

                Section::make('Системная информация')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('UUID')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label('Создан')
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->label('Обновлён')
                            ->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
