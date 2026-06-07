<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('news_posts', 'cover_media_id')) {
            Schema::table('news_posts', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('cover_media_id');
            });
        }

        if (Schema::hasColumn('news_post_blocks', 'media_id')) {
            Schema::table('news_post_blocks', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('media_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('news_posts', 'cover_media_id')) {
            Schema::table('news_posts', function (Blueprint $table): void {
                $table->foreignId('cover_media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('news_post_blocks', 'media_id')) {
            Schema::table('news_post_blocks', function (Blueprint $table): void {
                $table->foreignId('media_id')
                    ->nullable()
                    ->constrained('media')
                    ->nullOnDelete();
            });
        }
    }
};
