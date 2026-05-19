<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('category_attribute_definitions', function (Blueprint $table): void {
            $table->json('dependency_rules')
                ->nullable()
                ->after('default_value');
            $table->unsignedInteger('schema_version')
                ->default(1)
                ->after('applies_to_children');
        });

        Schema::table('listing_attribute_values', function (Blueprint $table): void {
            $table->unsignedInteger('attribute_schema_version')
                ->default(1)
                ->after('attribute_definition_id');
            $table->json('attribute_snapshot')
                ->nullable()
                ->after('attribute_schema_version');
        });
    }

    public function down(): void
    {
        Schema::table('listing_attribute_values', function (Blueprint $table): void {
            $table->dropColumn([
                'attribute_schema_version',
                'attribute_snapshot',
            ]);
        });

        Schema::table('category_attribute_definitions', function (Blueprint $table): void {
            $table->dropColumn([
                'dependency_rules',
                'schema_version',
            ]);
        });
    }
};
