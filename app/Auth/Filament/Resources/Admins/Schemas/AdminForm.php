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
                            ->label('Имя')
                            ->placeholder('Например, Imran')
                            ->prefixIcon(Heroicon::OutlinedUser)
                            ->autofocus()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('admin@example.com')
                            ->email()
                            ->prefixIcon(Heroicon::OutlinedEnvelope)
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Безопасность')
                    ->description('Пароль обязателен при создании. При редактировании можно оставить пустым, если менять его не требуется.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->saved(fn(?string $state): bool => filled($state))
                            ->confirmed(),

                        TextInput::make('password_confirmation')
                            ->label('Подтверждение пароля')
                            ->password()
                            ->revealable()
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
