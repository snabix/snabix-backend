<?php

declare(strict_types=1);

namespace App\Auth\Filament\Resources\Admins\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class AdminForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Основная информация')
                    ->description('Базовые данные администратора для входа и управления платформой.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->autofocus()
                            ->translateLabel()
                            ->string()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('email')
                            ->email()
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->required()
                            ->translateLabel()
                            ->string()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Безопасность')
                    ->description('Пароль обязателен при создании. При редактировании можно оставить пустым, если менять его не требуется.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->translateLabel()
                            ->string()
                            ->maxLength(255)
                            ->saved(fn(?string $state): bool => filled($state))
                            ->confirmed(),

                        TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->translateLabel()
                            ->string()
                            ->maxLength(255)
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->same('password')
                            ->saved(false),
                    ]),

                Section::make('Статус верификации')
                    ->description('При необходимости можно вручную отметить почту администратора как подтверждённую.')
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Подтверждено в')
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Оставьте поле пустым, если почта ещё не подтверждена.'),
                    ]),
            ]);
    }
}
