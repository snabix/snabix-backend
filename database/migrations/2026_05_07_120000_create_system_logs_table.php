<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('level', 32);
            $table->string('category', 100);
            $table->string('action')->nullable();
            $table->text('message');
            $table->json('context')->nullable();
            $table->string('route_name')->nullable();
            $table->string('method', 16)->nullable();
            $table->string('path')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('user_id')->nullable()->index();
            $table->timestamps();

            $table->index(['category', 'created_at']);
            $table->index(['level', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};
