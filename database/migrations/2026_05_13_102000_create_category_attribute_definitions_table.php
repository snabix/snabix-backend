<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('category_attribute_definitions', function (Blueprint $table): void {
            $table->timestamps();
            $table->id();
            $table->foreignUuid('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedTinyInteger('type')
                ->default(1);
            $table->string('unit', 32)
                ->nullable();
            $table->text('description')
                ->nullable();
            $table->string('placeholder')
                ->nullable();
            $table->text('help_text')
                ->nullable();
            $table->json('default_value')
                ->nullable();
            $table->string('group_name', 120)
                ->nullable();
            $table->json('options')
                ->nullable();
            $table->boolean('is_required')
                ->default(false);
            $table->boolean('is_filterable')
                ->default(false);
            $table->boolean('show_in_card')
                ->default(false);
            $table->boolean('is_active')
                ->default(true);
            $table->boolean('applies_to_children')
                ->default(true);
            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->unique(['category_id', 'slug']);
            $table->index(['category_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attribute_definitions');
    }
};
