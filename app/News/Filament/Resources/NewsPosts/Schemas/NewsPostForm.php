<?php

declare(strict_types=1);

namespace App\News\Filament\Resources\NewsPosts\Schemas;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use App\News\Domain\Enums\NewsPostBlockType;
use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use App\News\Infrastructure\Models\EloquentNewsPostBlock;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class NewsPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(12)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Основная информация')
                            ->icon(Heroicon::OutlinedNewspaper)
                            ->columns()
                            ->columnSpan([
                                'lg' => 8,
                            ])
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
                                    ->disabled()
                                    ->dehydrated()
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

                                SpatieMediaLibraryFileUpload::make('cover')
                                    ->label('Обложка')
                                    ->collection(EloquentNewsPost::COVER_COLLECTION)
                                    ->image()
                                    ->disk('public')
                                    ->downloadable()
                                    ->openable()
                                    ->columnSpanFull()
                                    ->maxSize(1024 * 3),
                            ]),

                        Section::make('Публикация')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->columnSpan([
                                'default' => 12,
                                'lg'      => 4,
                            ])
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
                    ]),

                Section::make('Конструктор контента')
                    ->columnSpanFull()
                    ->icon(Heroicon::OutlinedSquares2x2)
                    ->description('Добавляйте блоки, меняйте порядок и используйте JSON-данные для нужного типа секции.')
                    ->schema([
                        Section::make('Пример JSON')
                            ->description('Откройте подсказку, чтобы посмотреть структуру data для каждого типа блока.')
                            ->icon(Heroicon::OutlinedCodeBracketSquare)
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Placeholder::make('block_examples')
                                    ->label('')
                                    ->content(self::examples()),
                            ]),

                        Repeater::make('blocks')
                            ->relationship()
                            ->label('Блоки статьи')
                            ->schema([
                                Grid::make()
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
                                    ]),

                                SpatieMediaLibraryFileUpload::make('attachment')
                                    ->label('Медиа')
                                    ->collection(EloquentNewsPostBlock::MEDIA_COLLECTION)
                                    ->disk('public')
                                    ->downloadable()
                                    ->openable()
                                    ->visible(fn(Get $get): bool => self::isImageBlockType($get('type')))
                                    ->maxSize(1024 * 3),

                                CodeEditor::make('data')
                                    ->label('Данные блока (JSON)')
                                    ->language(Language::Json)
                                    ->required()
                                    ->columnSpanFull()
                                    ->formatStateUsing(fn(mixed $state): string => self::jsonEncode($state))
                                    ->dehydrateStateUsing(fn(?string $state): array => self::jsonDecode($state))
                                    ->helperText('Редактор поддерживает подсветку JSON. Набор ключей зависит от выбранного типа блока.')
                                    ->rules(['json']),
                            ])
                            ->collapsed()
                            ->itemLabel(fn(array $state): ?string => self::blockItemLabel($state))
                            ->reorderable()
                            ->defaultItems(0),
                    ]),
            ]);
    }

    private static function examples(): HtmlString
    {
        $json        = json_encode([
            'lead'      => [
                'text' => 'Крупный вводный текст материала',
            ],
            'paragraph' => [
                'text' => 'Обычный абзац',
            ],
            'quote'     => [
                'text'   => 'Цитата',
                'author' => 'Snabix',
            ],
            'split'     => [
                'items' => [
                    [
                        'title' => 'Первый тезис',
                        'text'  => 'Описание первого тезиса',
                    ],
                    [
                        'title' => 'Второй тезис',
                        'text'  => 'Описание второго тезиса',
                    ],
                ],
            ],
            'steps'     => [
                'items' => [
                    [
                        'title' => 'Заголовок шага',
                        'text'  => 'Описание шага',
                    ],
                ],
            ],
            'metrics'   => [
                'items' => [
                    [
                        'label' => 'показатель',
                        'value' => '42%',
                    ],
                ],
            ],
            'image'     => [
                'caption'  => 'Подпись к изображению',
                'imageUrl' => 'https://example.com/image.jpg',
            ],
            'gallery'   => [
                'items' => [
                    [
                        'imageUrl' => 'https://example.com/gallery-1.jpg',
                        'caption'  => 'Первое изображение',
                    ],
                ],
            ],
            'table'     => [
                'columns' => ['Поле', 'Значение'],
                'rows'    => [
                    ['Статус', 'Опубликовано'],
                ],
            ],
            'imageGrid' => [
                'items' => [
                    [
                        'title'    => 'Карточка с изображением',
                        'text'     => 'Описание карточки',
                        'imageUrl' => 'https://example.com/grid.jpg',
                        'caption'  => 'Подпись',
                    ],
                ],
            ],
            'cta'       => [
                'title'       => 'Готовы начать?',
                'text'        => 'Создайте объявление',
                'buttonLabel' => 'Разместить',
                'href'        => '/account/listings/create',
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $escapedJson = e(is_string($json) ? $json : '{}');

        return new HtmlString(
            <<<HTML
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-950 shadow-sm dark:border-white/10">
                    <div class="flex items-center justify-between border-b border-white/10 bg-white/[0.04] px-4 py-2">
                        <span class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-300">examples.json</span>
                        <span class="rounded-full bg-emerald-400/10 px-2 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-emerald-200">JSON</span>
                    </div>
                    <pre class="max-h-[360px] overflow-auto p-4 text-[13px] leading-6 text-slate-100"><code>{$escapedJson}</code></pre>
                </div>
                HTML,
        );
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

    private static function isImageBlockType(mixed $value): bool
    {
        return is_numeric($value) && (int) $value === NewsPostBlockType::IMAGE->value;
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
