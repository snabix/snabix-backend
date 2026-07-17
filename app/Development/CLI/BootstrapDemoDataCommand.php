<?php

declare(strict_types=1);

namespace App\Development\CLI;

use App\Auth\Infrastructure\Models\EloquentAdmin;
use Database\Seeders\ListingsDemoSeeder;
use Database\Seeders\NewsDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'app:bootstrap-demo-data')]
class BootstrapDemoDataCommand extends Command
{
    protected $signature   = 'app:bootstrap-demo-data
        {--admin-name= : Имя администратора}
        {--admin-email= : Email администратора}
        {--admin-password= : Пароль администратора}
        {--regions= : Путь к russia-regions.json}
        {--cities= : Путь к russia-cities.json}
        {--fresh-locations : Очистить регионы и города перед импортом}
        {--category-source=prom.ua : Идентификатор источника категорий}
        {--category-url=https://prom.ua/consumer-goods : URL публичного каталога}
        {--skip-location-import : Не запускать импорт регионов и городов}
        {--skip-category-import : Не запускать импорт категорий}
        {--skip-listings : Не создавать demo-объявления}
        {--skip-news : Не создавать demo-новости}';

    protected $description = 'Централизованно подготовить demo/dev данные, справочники и администратора';

    public function handle(): int
    {
        if (! (bool) $this->option('skip-location-import') && $this->importLocations() !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! (bool) $this->option('skip-category-import') && $this->importCategories() !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! $this->createOrUpdateAdmin()) {
            return self::FAILURE;
        }

        if (! (bool) $this->option('skip-listings')) {
            $this->components->info('Создаём demo-объявления после подготовки категорий...');
            $this->call(ListingsDemoSeeder::class);
        }

        if (! (bool) $this->option('skip-news')) {
            $this->components->info('Создаём demo-новости...');
            $this->call(NewsDemoSeeder::class);
        }

        $this->components->info('Bootstrap demo/dev данных завершён.');

        return self::SUCCESS;
    }

    private function importLocations(): int
    {
        $arguments = [
            '--fresh' => (bool) $this->option('fresh-locations'),
        ];

        foreach (['regions', 'cities'] as $option) {
            $value = $this->option($option);

            if (is_string($value) && trim($value) !== '') {
                $arguments['--' . $option] = trim($value);
            }
        }

        $this->components->info('Импортируем регионы и города...');

        return $this->call('location:import-russia', $arguments);
    }

    private function importCategories(): int
    {
        $this->components->info('Импортируем категории...');

        return $this->call('catalog:import-categories', [
            '--source' => $this->stringOption('category-source', 'prom.ua'),
            '--url'    => $this->stringOption('category-url', 'https://prom.ua/consumer-goods'),
        ]);
    }

    private function createOrUpdateAdmin(): bool
    {
        $email    = $this->stringOption('admin-email', config('snabix-bootstrap.admin_email'));
        $name     = $this->stringOption('admin-name', config('snabix-bootstrap.admin_name'));
        $password = $this->stringOption('admin-password', config('snabix-bootstrap.admin_password'));

        if ($email === null || $password === null) {
            $this->components->error(
                'Укажите SNABIX_BOOTSTRAP_ADMIN_EMAIL и SNABIX_BOOTSTRAP_ADMIN_PASSWORD в .env или передайте --admin-email/--admin-password.',
            );

            return false;
        }

        $name ??= 'Admin';

        $admin    = EloquentAdmin::query()->updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
            ],
        );

        $role     = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        $this->components->info(sprintf('Администратор %s подготовлен и получил роль super_admin.', $email));

        return true;
    }

    private function stringOption(string $key, mixed $fallback = null): ?string
    {
        $value = $this->option($key);

        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return is_string($fallback) && trim($fallback) !== ''
            ? trim($fallback)
            : null;
    }
}
