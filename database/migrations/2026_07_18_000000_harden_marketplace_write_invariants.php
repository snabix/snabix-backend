<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** @var list<string> */
    private const array CONSTRAINTS = [
        'listings_type_check',
        'listings_status_check',
        'listings_condition_check',
        'listings_price_check',
        'listings_currency_check',
        'listings_views_count_check',
        'user_reviews_rating_check',
        'user_reviews_status_check',
        'user_reviews_distinct_participants_check',
        'users_seller_rating_avg_check',
        'users_seller_reviews_count_check',
        'users_seller_rating_aggregate_check',
    ];

    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->timestampsTz();
            $table->uuid('id')->primary();
            $table->string('scope', 64);
            $table->char('actor_key_hash', 64);
            $table->char('idempotency_key_hash', 64);
            $table->char('request_fingerprint', 64);
            $table->uuid('resource_id')->nullable();
            $table->timestampTz('expires_at');

            $table->unique(
                ['scope', 'actor_key_hash', 'idempotency_key_hash'],
                'idempotency_keys_scope_actor_key_hash_unique',
            );
            $table->index('expires_at');
        });

        $this->addCheck('listings', 'listings_type_check', 'type IN (1, 2)');
        $this->addCheck('listings', 'listings_status_check', 'status IN (1, 2, 3, 4, 5)');
        $this->addCheck('listings', 'listings_condition_check', 'condition IN (1, 2, 3)');
        $this->addCheck('listings', 'listings_price_check', 'price IS NULL OR price >= 0');
        $this->addCheck('listings', 'listings_currency_check', "currency ~ '^[A-Z]{3}$'");
        $this->addCheck('listings', 'listings_views_count_check', 'views_count >= 0');

        $this->addCheck('user_reviews', 'user_reviews_rating_check', 'rating BETWEEN 1 AND 5');
        $this->addCheck(
            'user_reviews',
            'user_reviews_status_check',
            "status IN ('published', 'hidden', 'rejected')",
        );
        $this->addCheck(
            'user_reviews',
            'user_reviews_distinct_participants_check',
            'reviewer_id <> reviewee_id',
        );

        $this->addCheck(
            'users',
            'users_seller_rating_avg_check',
            'seller_rating_avg IS NULL OR seller_rating_avg BETWEEN 1 AND 5',
        );
        $this->addCheck(
            'users',
            'users_seller_reviews_count_check',
            'seller_reviews_count >= 0',
        );
        $this->addCheck(
            'users',
            'users_seller_rating_aggregate_check',
            '(seller_reviews_count = 0 AND seller_rating_avg IS NULL) '
            . 'OR (seller_reviews_count > 0 AND seller_rating_avg IS NOT NULL)',
        );

        foreach (self::CONSTRAINTS as $constraint) {
            $table = str_starts_with($constraint, 'listings_')
                ? 'listings'
                : (str_starts_with($constraint, 'user_reviews_') ? 'user_reviews' : 'users');

            DB::statement(sprintf(
                'ALTER TABLE %s VALIDATE CONSTRAINT %s',
                $table,
                $constraint,
            ));
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::CONSTRAINTS) as $constraint) {
            $table = str_starts_with($constraint, 'listings_')
                ? 'listings'
                : (str_starts_with($constraint, 'user_reviews_') ? 'user_reviews' : 'users');

            DB::statement(sprintf(
                'ALTER TABLE %s DROP CONSTRAINT IF EXISTS %s',
                $table,
                $constraint,
            ));
        }

        Schema::dropIfExists('idempotency_keys');
    }

    private function addCheck(string $table, string $name, string $expression): void
    {
        DB::statement(sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s CHECK (%s) NOT VALID',
            $table,
            $name,
            $expression,
        ));
    }
};
