<?php

declare(strict_types=1);

namespace App\Listing\Domain\ValueObjects;

final readonly class ListingContact
{
    private function __construct(
        private ?string $name,
        private ?string $phone,
        private ?string $email,
    ) {}

    public static function from(
        ?string $name,
        ?string $phone,
        ?string $email,
    ): self {
        $normalizedEmail = self::normalize($email, 255);

        return new self(
            name: self::normalize($name, 120),
            phone: self::normalize($phone, 32),
            email: $normalizedEmail === null ? null : mb_strtolower($normalizedEmail),
        );
    }

    private static function normalize(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : mb_substr($normalized, 0, $limit);
    }

    /**
     * @return array{contact_name: ?string, contact_phone: ?string, contact_email: ?string}
     */
    public function toPersistence(): array
    {
        return [
            'contact_name'  => $this->name,
            'contact_phone' => $this->phone,
            'contact_email' => $this->email,
        ];
    }
}
