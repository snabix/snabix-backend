<?php

declare(strict_types=1);

namespace App\Listing\Infrastructure\Services;

use App\Listing\Infrastructure\Models\EloquentListing;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class ListingSlugGenerator
{
    public function generate(string $title, ?string $ignoreId = null): string
    {
        $baseSlug  = Str::slug($title);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => ['Не удалось сформировать slug для объявления.'],
            ]);
        }

        $candidate = $baseSlug;
        $counter   = 2;

        while (
            EloquentListing::query()
                ->where('slug', $candidate)
                ->when($ignoreId !== null, fn($query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $candidate = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $candidate;
    }
}
