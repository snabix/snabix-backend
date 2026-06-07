<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Catalog\Domain\Enums\CategoryAttributeType;
use App\Catalog\Domain\Enums\CategoryCatalogType;
use App\Catalog\Infrastructure\Models\EloquentCategory;
use App\Catalog\Infrastructure\Models\EloquentCategoryAttributeDefinition;
use Illuminate\Database\Seeder;

class CatalogDemoSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->seedCategories();

        $this->seedAttributeDefinitions($categories);
    }

    /**
     * @return array<string, EloquentCategory>
     */
    private function seedCategories(): array
    {
        $definitions = [
            ['slug' => 'tovary', 'parent' => null, 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Товары', 'description' => 'Корневая ветка товарных объявлений.', 'sort' => 1],
            ['slug' => 'uslugi', 'parent' => null, 'type' => CategoryCatalogType::SERVICE, 'name' => 'Услуги', 'description' => 'Корневая ветка сервисных объявлений.', 'sort' => 2],
            ['slug' => 'transport', 'parent' => 'tovary', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Транспорт', 'description' => 'Автомобили, мототехника и транспортные предложения.', 'sort' => 10],
            ['slug' => 'avtomobili', 'parent' => 'transport', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Автомобили', 'description' => 'Легковые автомобили с пробегом и новые предложения.', 'sort' => 10],
            ['slug' => 'avto-zapchasti', 'parent' => 'transport', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Автозапчасти', 'description' => 'Запчасти и комплектующие для автомобилей.', 'sort' => 20],
            ['slug' => 'elektronika', 'parent' => 'tovary', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Электроника', 'description' => 'Гаджеты, устройства и бытовая техника.', 'sort' => 20],
            ['slug' => 'smartfony', 'parent' => 'elektronika', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Смартфоны', 'description' => 'Мобильные телефоны и аксессуары.', 'sort' => 10],
            ['slug' => 'noutbuki', 'parent' => 'elektronika', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Ноутбуки', 'description' => 'Ноутбуки для работы, учебы и игр.', 'sort' => 20],
            ['slug' => 'nedvizhimost', 'parent' => 'tovary', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Недвижимость', 'description' => 'Продажа и аренда объектов недвижимости.', 'sort' => 30],
            ['slug' => 'kvartiry', 'parent' => 'nedvizhimost', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Квартиры', 'description' => 'Квартиры для продажи и аренды.', 'sort' => 10],
            ['slug' => 'dom-i-sad', 'parent' => 'tovary', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Дом и сад', 'description' => 'Товары для дома, ремонта и сада.', 'sort' => 40],
            ['slug' => 'mebel', 'parent' => 'dom-i-sad', 'type' => CategoryCatalogType::PRODUCT, 'name' => 'Мебель', 'description' => 'Домашняя и офисная мебель.', 'sort' => 10],
            ['slug' => 'remont-i-otdelka', 'parent' => 'uslugi', 'type' => CategoryCatalogType::SERVICE, 'name' => 'Ремонт и отделка', 'description' => 'Строительные и отделочные услуги.', 'sort' => 10],
            ['slug' => 'gruzoperevozki', 'parent' => 'uslugi', 'type' => CategoryCatalogType::SERVICE, 'name' => 'Грузоперевозки', 'description' => 'Транспортные и логистические услуги.', 'sort' => 20],
        ];

        $categories  = [];

        foreach ($definitions as $definition) {
            $parent                          = is_string($definition['parent'])
                ? ($categories[$definition['parent']] ?? EloquentCategory::query()->where('slug', $definition['parent'])->first())
                : null;
            $path                            = $parent instanceof EloquentCategory ? sprintf('%s/%s', $parent->path, $definition['slug']) : $definition['slug'];

            $categories[$definition['slug']] = EloquentCategory::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'parent_id'    => $parent?->id,
                    'catalog_type' => $definition['type'],
                    'name'         => $definition['name'],
                    'description'  => $definition['description'],
                    'sort_order'   => $definition['sort'],
                    'is_active'    => true,
                    'path'         => $path,
                    'depth'        => substr_count($path, '/'),
                ],
            );
        }

        return $categories;
    }

    /**
     * @param array<string, EloquentCategory> $categories
     */
    private function seedAttributeDefinitions(array $categories): void
    {
        $definitions = [
            ['category' => 'avtomobili', 'name' => 'Марка', 'slug' => 'marka', 'type' => CategoryAttributeType::SELECT, 'options' => ['Toyota', 'BMW', 'Lada', 'Hyundai', 'Mercedes-Benz'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'avtomobili', 'name' => 'Год выпуска', 'slug' => 'god-vypuska', 'type' => CategoryAttributeType::NUMBER, 'options' => null, 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 20],
            ['category' => 'avtomobili', 'name' => 'Пробег', 'slug' => 'probeg', 'type' => CategoryAttributeType::NUMBER, 'options' => null, 'unit' => 'км', 'required' => false, 'filterable' => true, 'card' => true, 'sort' => 30],
            ['category' => 'avto-zapchasti', 'name' => 'Марка автомобиля', 'slug' => 'marka-avtomobilya', 'type' => CategoryAttributeType::SELECT, 'options' => ['Toyota', 'BMW', 'Lada'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'avto-zapchasti', 'name' => 'Артикул детали', 'slug' => 'artikul-detali', 'type' => CategoryAttributeType::TEXT, 'options' => null, 'required' => true, 'filterable' => false, 'card' => false, 'sort' => 20],
            ['category' => 'smartfony', 'name' => 'Бренд', 'slug' => 'brend', 'type' => CategoryAttributeType::SELECT, 'options' => ['Apple', 'Samsung', 'Xiaomi', 'Honor', 'Realme'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'smartfony', 'name' => 'Объем памяти', 'slug' => 'obem-pamyati', 'type' => CategoryAttributeType::SELECT, 'options' => ['64 ГБ', '128 ГБ', '256 ГБ', '512 ГБ', '1 ТБ'], 'required' => false, 'filterable' => true, 'card' => true, 'sort' => 20],
            ['category' => 'noutbuki', 'name' => 'Производитель', 'slug' => 'proizvoditel', 'type' => CategoryAttributeType::SELECT, 'options' => ['Apple', 'ASUS', 'Lenovo', 'HP', 'Acer'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'noutbuki', 'name' => 'Диагональ экрана', 'slug' => 'diagonal-ekrana', 'type' => CategoryAttributeType::SELECT, 'options' => ['13"', '14"', '15.6"', '16"', '17"'], 'required' => false, 'filterable' => true, 'card' => true, 'sort' => 20],
            ['category' => 'kvartiry', 'name' => 'Количество комнат', 'slug' => 'kolichestvo-komnat', 'type' => CategoryAttributeType::SELECT, 'options' => ['Студия', '1', '2', '3', '4+'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'kvartiry', 'name' => 'Площадь', 'slug' => 'ploshchad', 'type' => CategoryAttributeType::NUMBER, 'options' => null, 'unit' => 'м²', 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 20],
            ['category' => 'mebel', 'name' => 'Материал', 'slug' => 'material', 'type' => CategoryAttributeType::SELECT, 'options' => ['Массив', 'ЛДСП', 'Металл', 'Стекло', 'Ткань'], 'required' => false, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'mebel', 'name' => 'Ширина', 'slug' => 'shirina', 'type' => CategoryAttributeType::NUMBER, 'options' => null, 'unit' => 'см', 'required' => false, 'filterable' => false, 'card' => false, 'sort' => 20],
            ['category' => 'remont-i-otdelka', 'name' => 'Формат работы', 'slug' => 'format-raboty', 'type' => CategoryAttributeType::SELECT, 'options' => ['На объекте', 'Онлайн', 'С выездом'], 'required' => true, 'filterable' => true, 'card' => true, 'sort' => 10],
            ['category' => 'gruzoperevozki', 'name' => 'Грузоподъемность', 'slug' => 'gruzopodemnost', 'type' => CategoryAttributeType::NUMBER, 'options' => null, 'unit' => 'т', 'required' => false, 'filterable' => true, 'card' => true, 'sort' => 10],
        ];

        foreach ($definitions as $definition) {
            $category = $categories[$definition['category']] ?? EloquentCategory::query()->where('slug', $definition['category'])->first();

            if (! $category instanceof EloquentCategory) {
                continue;
            }

            EloquentCategoryAttributeDefinition::query()->updateOrCreate(
                ['category_id' => $category->id, 'slug' => $definition['slug']],
                [
                    'name'                => $definition['name'],
                    'type'                => $definition['type'],
                    'unit'                => $definition['unit'] ?? null,
                    'description'         => sprintf('Подготовленная характеристика для категории "%s".', $category->name),
                    'placeholder'         => null,
                    'help_text'           => null,
                    'default_value'       => null,
                    'group_name'          => 'Основные',
                    'options'             => $definition['options'],
                    'is_required'         => $definition['required'],
                    'is_filterable'       => $definition['filterable'],
                    'show_in_card'        => $definition['card'],
                    'is_active'           => true,
                    'applies_to_children' => true,
                    'sort_order'          => $definition['sort'],
                ],
            );
        }
    }
}
