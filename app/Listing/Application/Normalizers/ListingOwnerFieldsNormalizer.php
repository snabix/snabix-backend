<?php

declare(strict_types=1);

namespace App\Listing\Application\Normalizers;

use App\Listing\Domain\ValueObjects\ListingContact;
use App\Listing\Domain\ValueObjects\ListingCurrency;
use App\Listing\Domain\ValueObjects\ListingPrice;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

final readonly class ListingOwnerFieldsNormalizer
{
    /**
     * @param array{
     *     title: string,
     *     description: string,
     *     price: ?int,
     *     currency: ?string,
     *     is_negotiable: bool,
     *     contact_name: ?string,
     *     contact_phone: ?string,
     *     contact_email: ?string
     * } $attributes
     * @param  array<string, mixed> $address
     * @return array<string, mixed>
     */
    public function normalize(array $attributes, array $address): array
    {
        $price    = $this->price($attributes['price']);
        $currency = $this->currency($attributes['currency']);
        $contact  = ListingContact::from(
            $attributes['contact_name'],
            $attributes['contact_phone'],
            $attributes['contact_email'],
        );

        return [
            'title'              => $this->requiredText($attributes['title'], 'title', 'Заголовок объявления обязателен.'),
            'description'        => $this->requiredText($attributes['description'], 'description', 'Описание объявления обязательно.'),
            'price'              => $price->value(),
            'currency'           => $currency->value(),
            'is_negotiable'      => $attributes['is_negotiable'],
            ...$contact->toPersistence(),
            'profile_address_id' => $this->nullableString($address['profile_address_id'] ?? null),
            'region_id'          => $this->nullableInteger($address['region_id'] ?? null),
            'city_id'            => $this->nullableInteger($address['city_id'] ?? null),
            'address_snapshot'   => $this->nullableObjectArray($address['address_snapshot'] ?? null),
        ];
    }

    private function price(?int $price): ListingPrice
    {
        try {
            return ListingPrice::from($price);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'price' => [$exception->getMessage()],
            ]);
        }
    }

    private function currency(?string $currency): ListingCurrency
    {
        try {
            return ListingCurrency::from($currency);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'currency' => [$exception->getMessage()],
            ]);
        }
    }

    private function requiredText(string $value, string $field, string $message): string
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                $field => [$message],
            ]);
        }

        return $normalized;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }

    private function nullableInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        return is_string($value) && is_numeric($value) ? (int) $value : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function nullableObjectArray(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $normalized = [];

        foreach ($value as $key => $item) {
            if (! is_string($key)) {
                return null;
            }

            $normalized[$key] = $item;
        }

        return $normalized;
    }
}
