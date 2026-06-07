<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listing_attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
            $table->foreignUuid('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();
            $table->foreignId('attribute_definition_id')
                ->constrained('category_attribute_definitions')
                ->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->string('display_value')->nullable();

            $table->unique(['listing_id', 'attribute_definition_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_attribute_values');
    }
};
