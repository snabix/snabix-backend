<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->nullableUuidMorphs('model');
            $table->uuid()
                ->nullable()
                ->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')
                ->nullable();
            $table->string('disk');
            $table->string('conversions_disk')
                ->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedTinyInteger('media_type')
                ->default(4)
                ->index();
            $table->unsignedTinyInteger('visibility')
                ->default(1)
                ->index();
            $table->foreignId('uploaded_by_admin_id')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
            $table->text('description')
                ->nullable();
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->nullableTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
