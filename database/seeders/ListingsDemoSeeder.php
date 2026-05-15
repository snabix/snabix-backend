<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Infrastructure\Models\EloquentUser;
use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Database\Seeder;

class ListingsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (EloquentListing::query()->count() >= 20) {
            return;
        }

        if (EloquentUser::query()->count() < 5) {
            EloquentUser::factory()->count(5)->create();
        }

        [$autoParts, $furniture, $repairServices, $cargoServices] = $this->ensureCategories();

        $this->ensureAttributeDefinitions($autoParts, $furniture, $repairServices, $cargoServices);

        EloquentListing::factory()
            ->count(20)
            ->create();
    }

    /**
     * @return array{0:EloquentCategory,1:EloquentCategory,2:EloquentCategory,3:EloquentCategory}
     */
    private function ensureCategories(): array
    {
        $productsRoot   = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'tovary'],
            [
                'parent_id'    => null,
                'catalog_type' => CategoryCatalogType::PRODUCT,
                'name'         => 'Товары',
                'description'  => 'Корневая ветка товарных объявлений.',
                'sort_order'   => 1,
                'is_active'    => true,
                'path'         => 'tovary',
                'depth'        => 0,
            ],
        );

        $servicesRoot   = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'uslugi'],
            [
                'parent_id'    => null,
                'catalog_type' => CategoryCatalogType::SERVICE,
                'name'         => 'Услуги',
                'description'  => 'Корневая ветка сервисных объявлений.',
                'sort_order'   => 2,
                'is_active'    => true,
                'path'         => 'uslugi',
                'depth'        => 0,
            ],
        );

        $autoParts      = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'avto-zapchasti'],
            [
                'parent_id'    => $productsRoot->id,
                'catalog_type' => CategoryCatalogType::PRODUCT,
                'name'         => 'Автозапчасти',
                'description'  => 'Запчасти и комплектующие для автомобилей.',
                'sort_order'   => 10,
                'is_active'    => true,
                'path'         => 'tovary/avto-zapchasti',
                'depth'        => 1,
            ],
        );

        $furniture      = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'mebel'],
            [
                'parent_id'    => $productsRoot->id,
                'catalog_type' => CategoryCatalogType::PRODUCT,
                'name'         => 'Мебель',
                'description'  => 'Домашняя и офисная мебель.',
                'sort_order'   => 20,
                'is_active'    => true,
                'path'         => 'tovary/mebel',
                'depth'        => 1,
            ],
        );

        $repairServices = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'remont-i-otdelka'],
            [
                'parent_id'    => $servicesRoot->id,
                'catalog_type' => CategoryCatalogType::SERVICE,
                'name'         => 'Ремонт и отделка',
                'description'  => 'Строительные и отделочные услуги.',
                'sort_order'   => 10,
                'is_active'    => true,
                'path'         => 'uslugi/remont-i-otdelka',
                'depth'        => 1,
            ],
        );

        $cargoServices  = EloquentCategory::query()->firstOrCreate(
            ['slug' => 'gruzoperevozki'],
            [
                'parent_id'    => $servicesRoot->id,
                'catalog_type' => CategoryCatalogType::SERVICE,
                'name'         => 'Грузоперевозки',
                'description'  => 'Транспортные и логистические услуги.',
                'sort_order'   => 20,
                'is_active'    => true,
                'path'         => 'uslugi/gruzoperevozki',
                'depth'        => 1,
            ],
        );

        return [$autoParts, $furniture, $repairServices, $cargoServices];
    }

    private function ensureAttributeDefinitions(
        EloquentCategory $autoParts,
        EloquentCategory $furniture,
        EloquentCategory $repairServices,
        EloquentCategory $cargoServices,
    ): void {
        $definitions = [
            [$autoParts->id, 'Марка автомобиля', 'marka-avtomobilya', CategoryAttributeType::SELECT, ['Toyota', 'BMW', 'Lada'], true],
            [$autoParts->id, 'Артикул детали', 'artikul-detali', CategoryAttributeType::TEXT, null, true],
            [$furniture->id, 'Материал', 'material', CategoryAttributeType::SELECT, ['Массив', 'ЛДСП', 'Металл'], false],
            [$furniture->id, 'Ширина (см)', 'shirina-sm', CategoryAttributeType::NUMBER, null, false],
            [$repairServices->id, 'Формат работы', 'format-raboty', CategoryAttributeType::SELECT, ['На объекте', 'Онлайн', 'С выездом'], true],
            [$cargoServices->id, 'Грузоподъёмность', 'gruzopodyomnost', CategoryAttributeType::NUMBER, null, false],
        ];

        foreach ($definitions as [$categoryId, $name, $slug, $type, $options, $required]) {
            EloquentCategoryAttributeDefinition::query()->firstOrCreate(
                ['category_id' => $categoryId, 'slug' => $slug],
                [
                    'name'                => $name,
                    'type'                => $type,
                    'unit'                => null,
                    'description'         => null,
                    'placeholder'         => null,
                    'help_text'           => null,
                    'default_value'       => null,
                    'group_name'          => 'Основные',
                    'options'             => $options,
                    'is_required'         => $required,
                    'is_filterable'       => true,
                    'show_in_card'        => true,
                    'is_active'           => true,
                    'applies_to_children' => true,
                    'sort_order'          => 10,
                ],
            );
        }
    }
}
