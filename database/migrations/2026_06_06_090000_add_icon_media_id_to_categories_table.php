<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Compatibility stub for environments where this migration was already
        // recorded in the migrations table before the icon_media_id approach
        // was replaced with a polymorphic media relation.
    }

    public function down(): void
    {
        if (! Schema::hasColumn('categories', 'icon_media_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('icon_media_id');
        });
    }
};
