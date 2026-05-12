<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Catalog\Infrastructure\Models\EloquentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<EloquentCategory>
 */
class EloquentCategoryFactory extends Factory
{
    protected $model = EloquentCategory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Электроника',
            'Телефоны',
            'Компьютеры',
            'Бытовая техника',
            'Одежда',
            'Обувь',
            'Детские товары',
            'Автотовары',
            'Спорт и отдых',
            'Красота и здоровье',
            'Дом и сад',
            'Строительство',
            'Инструменты',
            'Зоотовары',
            'Канцтовары',
            'Мебель',
            'Освещение',
            'Туризм',
            'Книги',
            'Подарки',
        ];

        $name = $names[fake()->numberBetween(0, count($names) - 1)]
            . ' '
            . (string) fake()->unique()->numberBetween(1, 999);

        $slug = Str::slug($name);

        return [
            'parent_id'   => null,
            'name'        => $name,
            'slug'        => $slug !== '' ? $slug : 'kategoriya-' . fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'sort_order'  => fake()->numberBetween(0, 50),
            'is_active'   => fake()->boolean(90),
            'path'        => $slug !== '' ? $slug : null,
            'depth'       => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn(): array => [
            'is_active' => false,
        ]);
    }

    public function childOf(EloquentCategory $parent): static
    {
        return $this->state(function (array $attributes) use ($parent): array {
            $name = $attributes['name'] ?? null;
            $slug = $attributes['slug'] ?? null;

            $resolvedSlug = is_string($slug) && $slug !== ''
                ? $slug
                : Str::slug(is_string($name) ? $name : '');

            return [
                'parent_id'  => $parent->id,
                'sort_order' => fake()->numberBetween(0, 20),
                'depth'      => $parent->depth + 1,
                'path'       => $parent->path !== null ? $parent->path . '/' . $resolvedSlug : $resolvedSlug,
            ];
        });
    }
}
