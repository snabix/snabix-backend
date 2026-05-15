<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table): void {
            $table->timestampTz('published_at')
                ->nullable();
            $table->timestampTz('expires_at')
                ->nullable();
            $table->timestampsTz();
            $table->uuid('id')
                ->primary();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();
            $table->unsignedTinyInteger('type')
                ->index();
            $table->unsignedTinyInteger('status')
                ->default(1)
                ->index();
            $table->unsignedTinyInteger('condition')
                ->default(3);
            $table->string('title');
            $table->string('slug')
                ->unique();
            $table->text('description');
            $table->unsignedInteger('price')
                ->nullable();
            $table->char('currency', 3)
                ->default('RUB');
            $table->boolean('is_negotiable')
                ->default(false);
            $table->string('contact_name', 120)
                ->nullable();
            $table->string('contact_phone', 32)
                ->nullable();
            $table->string('contact_email')
                ->nullable();
            $table->unsignedInteger('views_count')
                ->default(0);
            $table->boolean('is_featured')
                ->default(false);
            $table->text('rejection_reason')
                ->nullable();

            $table->index(['category_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
