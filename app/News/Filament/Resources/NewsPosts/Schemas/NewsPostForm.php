<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Schemas;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\News\Domain\Enums\NewsPostBlockType;
use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use App\News\Infrastructure\Models\EloquentNewsPostBlock;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Основная информация')
                    ->icon(Heroicon::OutlinedNewspaper)
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Заголовок')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            })
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn(?string $state, Get $get): string => Str::slug($state ?: self::nullableString($get('title')) ?? '')),

                        TextInput::make('category')
                            ->label('Категория')
                            ->required()
                            ->maxLength(120),

                        TextInput::make('eyebrow')
                            ->label('Надзаголовок')
                            ->maxLength(120),

                        Textarea::make('description')
                            ->label('Краткое описание')
                            ->required()
                            ->rows(4)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Textarea::make('thesis')
                            ->label('Тезис')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),

                Section::make('Публикация')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Статус')
                            ->options(NewsPostStatus::options())
                            ->default(NewsPostStatus::DRAFT->value)
                            ->required()
                            ->native(false),

                        DateTimePicker::make('published_at')
                            ->label('Дата публикации')
                            ->seconds(false)
                            ->native(false),

                        TextInput::make('reading_time')
                            ->label('Время чтения')
                            ->placeholder('Например: 5 мин')
                            ->maxLength(40),

                        Toggle::make('is_featured')
                            ->label('Главный материал')
                            ->default(false),

                        SpatieMediaLibraryFileUpload::make('cover')
                            ->label('Обложка')
                            ->collection(EloquentNewsPost::COVER_COLLECTION)
                            ->image()
                            ->disk('public')
                            ->downloadable()
                            ->openable()
                            ->maxSize(1024 * 3),

                        Select::make('author_admin_id')
                            ->label('Автор')
                            ->options(fn(): array => EloquentAdmin::query()
                                ->orderBy('email')
                                ->get(['id', 'name', 'email'])
                                ->mapWithKeys(fn(EloquentAdmin $admin): array => [
                                    $admin->id => $admin->name . ' · ' . $admin->email,
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),

                Section::make('Конструктор контента')
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->description('Добавляйте блоки, меняйте порядок и используйте JSON-данные для нужного типа секции.')
                    ->schema([
                        Placeholder::make('block_examples')
                            ->label('Примеры данных')
                            ->content(self::examples()),

                        Repeater::make('blocks')
                            ->relationship()
                            ->label('Блоки статьи')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('type')
                                            ->label('Тип блока')
                                            ->options(NewsPostBlockType::options())
                                            ->required()
                                            ->live()
                                            ->native(false),

                                        TextInput::make('sort_order')
                                            ->label('Порядок')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->required(),

                                        SpatieMediaLibraryFileUpload::make('attachment')
                                            ->label('Медиа')
                                            ->collection(EloquentNewsPostBlock::MEDIA_COLLECTION)
                                            ->disk('public')
                                            ->downloadable()
                                            ->openable()
                                            ->maxSize(1024 * 3),
                                    ]),

                                Textarea::make('data')
                                    ->label('Данные блока (JSON)')
                                    ->rows(8)
                                    ->required()
                                    ->formatStateUsing(fn(mixed $state): string => self::jsonEncode($state))
                                    ->dehydrateStateUsing(fn(?string $state): array => self::jsonDecode($state))
                                    ->rules(['json']),
                            ])
                            ->collapsed()
                            ->itemLabel(fn(array $state): ?string => self::blockItemLabel($state))
                            ->reorderable()
                            ->defaultItems(0),
                    ]),
            ]);
    }

    private static function examples(): string
    {
        return <<<'TEXT'
            lead: {"text":"Крупный вводный текст материала"}
            paragraph: {"text":"Обычный абзац"}
            quote: {"text":"Цитата", "author":"Snabix"}
            split/steps/metrics: {"items":[{"title":"Заголовок","text":"Описание"}]}
            table: {"columns":["Поле","Значение"],"rows":[["Статус","Опубликовано"]]}
            gallery: {"items":[{"mediaId":1,"caption":"Описание"}]}
            cta: {"title":"Готовы начать?","text":"Создайте объявление","buttonLabel":"Разместить","href":"/account/listings/create"}
            TEXT;
    }

    /**
     * @param array<string, mixed> $state
     */
    private static function blockItemLabel(array $state): ?string
    {
        $type = $state['type'] ?? null;

        if (! is_numeric($type)) {
            return null;
        }

        return NewsPostBlockType::from((int) $type)->label();
    }

    private static function jsonEncode(mixed $state): string
    {
        if (is_array($state)) {
            return json_encode($state, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
        }

        return is_string($state) && $state !== '' ? $state : '{}';
    }

    /**
     * @return array<string, mixed>
     */
    private static function jsonDecode(?string $state): array
    {
        if ($state === null || trim($state) === '') {
            return [];
        }

        $decoded = json_decode($state, true);

        return is_array($decoded) ? $decoded : [];
    }

    private static function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        return (string) $value;
    }
}
