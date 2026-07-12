<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->decimal('seller_rating_avg', 3, 2)
                ->nullable()
                ->after('date_of_birth');
            $table->unsignedInteger('seller_reviews_count')
                ->default(0)
                ->after('seller_rating_avg');
        });

        Schema::create('user_reviews', function (Blueprint $table): void {
            $table->timestamps();
            $table->uuid('id')->primary();
            $table->foreignUuid('reviewer_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('reviewee_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUuid('listing_id')
                ->constrained('listings')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')
                ->nullable();
            $table->string('status', 32)
                ->default('published');
            $table->timestamp('published_at')
                ->nullable();

            $table->unique(['reviewer_id', 'listing_id']);
            $table->index(['reviewee_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reviews');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['seller_rating_avg', 'seller_reviews_count']);
        });
    }
};
