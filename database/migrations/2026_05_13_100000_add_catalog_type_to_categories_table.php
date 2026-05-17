<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('categories', 'catalog_type')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedTinyInteger('catalog_type')
                ->default(1)
                ->after('id')
                ->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'catalog_type')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('catalog_type');
        });
    }
};
