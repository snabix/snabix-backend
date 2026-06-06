<?php

declare(strict_types=1);

namespace Database\Factories;

use App\News\Domain\Enums\NewsPostStatus;
use App\News\Infrastructure\Models\EloquentNewsPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentNewsPost>
 */
class EloquentNewsPostFactory extends Factory
{
    protected $model = EloquentNewsPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'status'       => NewsPostStatus::PUBLISHED,
            'title'        => $title,
            'slug'         => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'category'     => fake()->randomElement(['Новости', 'Продукт', 'Безопасность']),
            'eyebrow'      => fake()->words(2, true),
            'description'  => fake()->sentence(14),
            'thesis'       => fake()->sentence(10),
            'reading_time' => fake()->numberBetween(2, 8) . ' мин',
            'is_featured'  => false,
            'views_count'  => 0,
            'published_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn(): array => [
            'status'       => NewsPostStatus::DRAFT,
            'published_at' => null,
        ]);
    }
}
