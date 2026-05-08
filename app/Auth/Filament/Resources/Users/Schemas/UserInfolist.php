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
                        TextEntry::make('first_name')
                            ->label('Имя'),

                        TextEntry::make('last_name')
                            ->label('Фамилия'),

                        TextEntry::make('email')
                            ->translateLabel()
                            ->copyable(),

                        TextEntry::make('phone_number')
                            ->label('Телефон')
                            ->placeholder('Не указан'),

                        IconEntry::make('is_active')
                            ->label('Аккаунт активен')
                            ->boolean(),

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
                            ->translateLabel()
                            ->dateTime('d.m.Y H:i'),

                        TextEntry::make('updated_at')
                            ->translateLabel()
                            ->dateTime('d.m.Y H:i'),
                    ]),
            ]);
    }
}
