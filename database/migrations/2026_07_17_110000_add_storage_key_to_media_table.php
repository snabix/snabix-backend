<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->string('storage_key')
                ->nullable()
                ->after('file_name');
            $table->unique(['disk', 'storage_key'], 'media_disk_storage_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table): void {
            $table->dropUnique('media_disk_storage_key_unique');
            $table->dropColumn('storage_key');
        });
    }
};
