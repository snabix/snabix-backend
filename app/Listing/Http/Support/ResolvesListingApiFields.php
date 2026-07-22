<?php

declare(strict_types=1);

namespace App\Listing\Http\Support;

use App\Listing\Domain\Enums\ListingCondition;
use App\Listing\Domain\Enums\ListingStatus;
use App\Listing\Domain\Enums\ListingType;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @mixin FormRequest
 */
trait ResolvesListingApiFields
{
    /**
     * @return list<string>
     */
    protected static function listingKindValues(): array
    {
        return array_map(
            static fn(ListingType $type): string => $type->apiName(),
            ListingType::cases(),
        );
    }

    /**
     * @return list<string>
     */
    protected static function listingStatusValues(): array
    {
        return array_map(
            static fn(ListingStatus $status): string => $status->apiName(),
            ListingStatus::cases(),
        );
    }

    /**
     * @return list<string>
     */
    protected static function itemConditionValues(): array
    {
        return array_map(
            static fn(ListingCondition $condition): string => $condition->apiName(),
            ListingCondition::cases(),
        );
    }

    protected function listingTypeValue(): int
    {
        $listingKind = $this->input('listingKind');

        if (is_string($listingKind)) {
            $listingType = ListingType::fromApiName($listingKind);

            if ($listingType !== null) {
                return $listingType->value;
            }
        }

        return $this->integer('type');
    }

    protected function nullableListingTypeValue(): ?int
    {
        if ($this->exists('listingKind')) {
            $listingKind = $this->input('listingKind');

            return is_string($listingKind)
                ? ListingType::fromApiName($listingKind)?->value
                : null;
        }

        return $this->nullableIntegerInput('type');
    }

    protected function nullableListingStatusValue(): ?int
    {
        if ($this->exists('listingStatus')) {
            $listingStatus = $this->input('listingStatus');

            return is_string($listingStatus)
                ? ListingStatus::fromApiName($listingStatus)?->value
                : null;
        }

        return $this->nullableIntegerInput('status');
    }

    protected function nullableListingConditionValue(): ?int
    {
        if ($this->exists('itemCondition')) {
            $itemCondition = $this->input('itemCondition');

            return is_string($itemCondition)
                ? ListingCondition::fromApiName($itemCondition)?->value
                : null;
        }

        return $this->nullableIntegerInput('condition');
    }

    protected function nullableMoneyAmount(string $canonicalKey, string $legacyKey): ?int
    {
        return $this->nullableIntegerInput(
            $this->exists($canonicalKey) ? $canonicalKey : $legacyKey,
        );
    }

    protected function nullableMoneyCurrency(string $canonicalKey, string $legacyKey): ?string
    {
        return $this->nullableUppercaseString(
            $this->exists($canonicalKey) ? $canonicalKey : $legacyKey,
        );
    }

    protected function nullableIntegerInput(string $key): ?int
    {
        $value = $this->input($key);

        return is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
    }

    protected function nullableUppercaseString(string $key): ?string
    {
        $value = $this->input($key);

        return is_string($value) && $value !== '' ? mb_strtoupper($value) : null;
    }
}
