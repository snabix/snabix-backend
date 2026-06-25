<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->timestamps();
            $table->timestamp('read_at')
                ->nullable();

            $table->uuid('id')
                ->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->json('data');

        });

        Schema::create('notification_preferences', function (Blueprint $table): void {
            $table->timestamps();
            $table->id();
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('event_key', 80);
            $table->boolean('site_enabled');
            $table->boolean('email_enabled');

            $table->unique(['user_id', 'event_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('notifications');
    }
};
