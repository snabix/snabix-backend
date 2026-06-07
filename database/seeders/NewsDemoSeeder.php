<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\News\Domain\Enums\NewsPostBlockType;
use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use UnexpectedValueException;

class NewsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentNewsPost::query()->count() >= 20) {
            return;
        }

        foreach ($this->posts() as $postData) {
            $blocks           = $this->blocks($postData);
            $publishedDaysAgo = $this->publishedDaysAgo($postData);
            $slug             = $this->slug($postData);
            unset($postData['blocks']);
            unset($postData['published_days_ago']);

            if (EloquentNewsPost::query()->where('slug', Str::slug($slug))->exists()) {
                continue;
            }

            /** @var EloquentNewsPost $post */
            $post             = EloquentNewsPost::query()->create([
                ...$postData,
                'slug'         => Str::slug($slug),
                'status'       => NewsPostStatus::PUBLISHED,
                'published_at' => now()->subDays($publishedDaysAgo),
            ]);

            foreach ($blocks as $index => $block) {
                if (! is_array($block)) {
                    throw new UnexpectedValueException('News demo block must be an array.');
                }

                $type = $block['type'] ?? null;
                $data = $block['data'] ?? null;

                if (! $type instanceof NewsPostBlockType || ! is_array($data)) {
                    throw new UnexpectedValueException('News demo block has invalid type or data.');
                }

                $post->blocks()->create([
                    'type'       => $type,
                    'sort_order' => $index + 1,
                    'data'       => $data,
                ]);
            }
        }
    }

    /**
     * @param  array<string, mixed> $postData
     * @return array<int, mixed>
     */
    private function blocks(array $postData): array
    {
        $blocks = $postData['blocks'] ?? null;

        if (! is_array($blocks)) {
            throw new UnexpectedValueException('News demo post blocks must be an array.');
        }

        return array_values($blocks);
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function publishedDaysAgo(array $postData): int
    {
        $publishedDaysAgo = $postData['published_days_ago'] ?? null;

        if (! is_int($publishedDaysAgo)) {
            throw new UnexpectedValueException('News demo published_days_ago must be an integer.');
        }

        return $publishedDaysAgo;
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function slug(array $postData): string
    {
        $slug = $postData['slug'] ?? null;

        if (! is_string($slug)) {
            throw new UnexpectedValueException('News demo slug must be a string.');
        }

        return $slug;
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function title(array $postData): string
    {
        $title = $postData['title'] ?? null;

        if (! is_string($title)) {
            throw new UnexpectedValueException('News demo title must be a string.');
        }

        return $title;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function posts(): array
    {
        $topics = [
            [
                'title'              => 'Marketplace доверия: каким мы видим Snabix',
                'slug'               => 'marketplace-doveriya',
                'category'           => 'Новости',
                'eyebrow'            => 'Product vision',
                'description'        => 'Рассказываем, почему Snabix строится вокруг доверия, аккуратных карточек и понятного локального каталога.',
                'thesis'             => 'Доверие должно считываться быстрее, чем пользователь успеет устать от выбора.',
                'reading_time'       => '4 мин',
                'is_featured'        => true,
                'views_count'        => 120,
                'published_days_ago' => 3,
            ],
            [
                'title'              => 'Конструктор новостей: как мы будем собирать материалы',
                'slug'               => 'konstruktor-novostey',
                'category'           => 'Архитектура',
                'eyebrow'            => 'Admin builder',
                'description'        => 'Как может выглядеть будущий конструктор новостей в админке и почему лучше хранить материал блоками.',
                'thesis'             => 'Редактор новостей должен быть достаточно гибким для контента и достаточно строгим для единого стиля.',
                'reading_time'       => '6 мин',
                'is_featured'        => false,
                'views_count'        => 64,
                'published_days_ago' => 1,
            ],
            $this->topic('Как подготовить объявление, которое хочется открыть', 'kak-podgotovit-obyavlenie', 'Инструкция', 'User guide', 'Короткий гид по созданию объявления: категория, фото, цена, характеристики и отправка на проверку.', 'Форма создания должна помогать продавцу, а не проверять его терпение.', '5 мин', 2),
            $this->topic('Доверие к продавцу: что важно показать в профиле', 'doverie-k-prodavcu', 'Безопасность', 'Trust mechanics', 'Какие сигналы помогут покупателю быстрее понять, стоит ли писать продавцу.', 'Покупатель должен понимать, кому он пишет, еще до первого сообщения.', '3 мин', 4),
            $this->topic('Товары и услуги: как мы проектируем каталог', 'tovary-i-uslugi', 'Продукт', 'Catalog system', 'Почему товары и услуги должны жить в одной платформе, но использовать разные формы и характеристики.', 'Разные категории должны иметь разные формы, иначе marketplace быстро становится шумным.', '4 мин', 5),
            $this->topic('Личный кабинет как центр управления объявлениями', 'lichnyy-kabinet', 'Обновления', 'Account updates', 'Избранное, медиа объявлений, фильтры и будущие отзывы постепенно складываются в личный кабинет продавца.', 'Личный кабинет должен быть панелью управления, а не набором случайных страниц.', '2 мин', 6),
            $this->topic('Как мы думаем о модерации объявлений', 'moderatsiya-obyavleniy', 'Безопасность', 'Moderation', 'Статусы, причины отклонения и повторная отправка помогают сделать публикацию предсказуемой.', 'Модерация должна объяснять следующий шаг, а не просто запрещать действие.', '4 мин', 7),
            $this->topic('Медиа в объявлениях: зачем нужна единая библиотека', 'media-v-obyavleniyah', 'Архитектура', 'Media library', 'Обложки, галереи, документы и будущие видео лучше хранить через единый слой медиа.', 'Медиафайлы становятся частью платформы, а не случайными загрузками в форме.', '5 мин', 8),
            $this->topic('Категории и характеристики: меньше шума в форме', 'kategorii-i-harakteristiki', 'Продукт', 'Dynamic forms', 'Дерево категорий и характеристики помогают пользователю заполнить только нужные поля.', 'Хорошая форма прячет лишнее и подсвечивает важное.', '5 мин', 9),
            $this->topic('Избранное как личный список решений', 'izbrannoe-kak-spisok-resheniy', 'Продукт', 'Favorites', 'Избранное помогает пользователю сравнивать предложения без повторного поиска.', 'Сохраненная карточка должна возвращать пользователя к контексту выбора.', '3 мин', 10),
            $this->topic('Адреса профиля и локальный поиск', 'adresa-profilya-i-lokalnyy-poisk', 'Обновления', 'Locations', 'Региональность marketplace начинается с аккуратного хранения адресов и городов.', 'Локальная платформа должна понимать расстояние, район и привычные сценарии поиска.', '4 мин', 11),
            $this->topic('Сессии и безопасность аккаунта', 'sessii-i-bezopasnost-akkaunta', 'Безопасность', 'Sessions', 'Пользователь должен видеть активные устройства и завершать лишние сессии без поддержки.', 'Безопасность становится понятнее, когда она отображается как список действий.', '4 мин', 12),
            $this->topic('Публичная карточка объявления без лишних полей', 'publichnaya-kartochka-obyavleniya', 'Продукт', 'Public DTO', 'Публичный API показывает покупателю только безопасные и полезные поля объявления.', 'Контактные данные должны открываться там, где пользователь готов к диалогу.', '3 мин', 13),
            $this->topic('Почему новости собираются блоками', 'pochemu-novosti-blokami', 'Архитектура', 'Content blocks', 'Блочный подход позволяет быстро менять материал и сохранять единый визуальный стиль.', 'Контент должен быть гибким для редактора и строгим для интерфейса.', '6 мин', 14),
            $this->topic('Роль отзывов в marketplace доверия', 'rol-otzyvov-v-marketplace', 'Безопасность', 'Reviews', 'Отзывы будут помогать пользователю понимать реальный опыт взаимодействия с продавцом.', 'Рейтинг полезен только тогда, когда рядом есть понятный контекст.', '4 мин', 15),
            $this->topic('Что важно в мобильной версии Snabix', 'mobilnaya-versiya-snabix', 'Продукт', 'Mobile UX', 'Мобильный интерфейс должен сохранять скорость поиска и удобство управления объявлениями.', 'На телефоне каждый лишний шаг чувствуется сильнее.', '3 мин', 16),
            $this->topic('Как мы проектируем пустые состояния', 'pustye-sostoyaniya', 'Дизайн', 'Empty states', 'Пустая страница может объяснить ситуацию и мягко предложить следующее действие.', 'Empty state должен помогать, а не выглядеть как техническая ошибка.', '3 мин', 17),
            $this->topic('Системное логирование важных действий', 'sistemnoe-logirovanie-deystviy', 'Архитектура', 'Activity logs', 'Важные действия пользователя фиксируются системно и не требуют ручного вызова в каждом методе.', 'Логи должны помогать разбираться в событиях, не превращаясь в шум.', '5 мин', 18),
            $this->topic('Первые шаги к продвижению объявлений', 'prodvizhenie-obyavleniy', 'Бизнес', 'Promotion', 'Платные поднятия, выделение и витрины должны быть встроены в продукт аккуратно.', 'Монетизация не должна ломать доверие к поисковой выдаче.', '4 мин', 19),
            $this->topic('Что дальше после каталога', 'chto-dalshe-posle-kataloga', 'Новости', 'Roadmap', 'После каталога мы постепенно собираем создание объявлений, медиа, новости и профиль в единую систему.', 'Хороший roadmap ощущается как последовательность понятных улучшений.', '4 мин', 20),
        ];

        return array_map(
            fn(array $post, int $index): array => [
                ...$post,
                'blocks' => $this->contentBlocks($index, $this->title($post)),
            ],
            $topics,
            array_keys($topics),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function topic(
        string $title,
        string $slug,
        string $category,
        string $eyebrow,
        string $description,
        string $thesis,
        string $readingTime,
        int $daysAgo,
    ): array {
        return [
            'title'              => $title,
            'slug'               => $slug,
            'category'           => $category,
            'eyebrow'            => $eyebrow,
            'description'        => $description,
            'thesis'             => $thesis,
            'reading_time'       => $readingTime,
            'is_featured'        => false,
            'views_count'        => 20 + ($daysAgo * 7),
            'published_days_ago' => $daysAgo,
        ];
    }

    /**
     * @return array<int, array{type: NewsPostBlockType, data: array<string, mixed>}>
     */
    private function contentBlocks(int $index, string $title): array
    {
        $lead      = [
            'type' => NewsPostBlockType::LEAD,
            'data' => [
                'text' => $title . ' — материал о том, как Snabix постепенно собирает локальный marketplace вокруг доверия, понятных действий и аккуратного пользовательского опыта.',
            ],
        ];

        $paragraph = [
            'type' => NewsPostBlockType::PARAGRAPH,
            'data' => [
                'text' => 'Мы смотрим на этот слой продукта как на часть общего сценария: пользователь должен быстро понимать, что происходит, почему это важно и какой следующий шаг доступен прямо сейчас.',
            ],
        ];

        $quote     = [
            'type' => NewsPostBlockType::QUOTE,
            'data' => [
                'text'   => 'Сильный marketplace не заставляет пользователя разбираться в системе. Он показывает контекст ровно в тот момент, когда он нужен.',
                'author' => 'Команда Snabix',
            ],
        ];

        $split     = [
            'type' => NewsPostBlockType::SPLIT,
            'data' => [
                'items' => [
                    ['title' => 'Понятный сценарий', 'text' => 'Каждая секция должна отвечать на конкретный вопрос пользователя и не перегружать интерфейс.'],
                    ['title' => 'Единый стиль', 'text' => 'Новости, объявления, профиль и админка должны ощущаться частями одного продукта.'],
                ],
            ],
        ];

        $steps     = [
            'type' => NewsPostBlockType::STEPS,
            'data' => [
                'items' => [
                    ['title' => 'Собрать данные', 'text' => 'Определяем, какие поля и состояния нужны для стабильного сценария.'],
                    ['title' => 'Показать главное', 'text' => 'Выводим ключевую информацию без лишнего визуального шума.'],
                    ['title' => 'Проверить поведение', 'text' => 'Покрываем API и важные пользовательские действия тестами.'],
                ],
            ],
        ];

        $metrics   = [
            'type' => NewsPostBlockType::METRICS,
            'data' => [
                'items' => [
                    ['label' => 'контент-блоков', 'value' => (string) (3 + ($index % 5))],
                    ['label' => 'фокус', 'value' => $index % 2 === 0 ? 'trust' : 'speed'],
                    ['label' => 'формат', 'value' => 'blocks'],
                ],
            ],
        ];

        $table     = [
            'type' => NewsPostBlockType::TABLE,
            'data' => [
                'columns' => ['Слой', 'Задача'],
                'rows'    => [
                    ['API', 'Стабильный DTO для frontend'],
                    ['Admin', 'Удобное управление контентом'],
                    ['UI', 'Единый рендер блоков'],
                ],
            ],
        ];

        $cta       = [
            'type' => NewsPostBlockType::CTA,
            'data' => [
                'title'       => 'Продолжить изучение Snabix',
                'text'        => 'Откройте список новостей или перейдите к объявлениям, чтобы увидеть продукт в действии.',
                'buttonLabel' => 'К объявлениям',
                'href'        => '/listings',
            ],
        ];

        return match ($index % 6) {
            0       => [$lead, $split, $quote, $metrics],
            1       => [$lead, $steps, $table],
            2       => [$lead, $paragraph, $split, $cta],
            3       => [$lead, $quote, $steps],
            4       => [$lead, $metrics, $paragraph, $table],
            default => [$lead, $split, $steps, $cta],
        };
    }
}
