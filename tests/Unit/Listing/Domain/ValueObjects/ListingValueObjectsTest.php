<?php

declare(strict_types=1);

namespace Tests\Unit\Listing\Domain\ValueObjects;

use App\Listing\Domain\ValueObjects\ListingContact;
use App\Listing\Domain\ValueObjects\ListingCurrency;
use App\Listing\Domain\ValueObjects\ListingPrice;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ListingValueObjectsTest extends TestCase
{
    public function test_price_preserves_nullable_integer_amount(): void
    {
        $this->assertNull(ListingPrice::from(null)->value());
        $this->assertSame(12500, ListingPrice::from(12500)->value());
    }

    public function test_price_rejects_negative_amount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ListingPrice::from(-1);
    }

    public function test_currency_uses_default_and_normalizes_code(): void
    {
        $this->assertSame('RUB', ListingCurrency::from(null)->value());
        $this->assertSame('USD', ListingCurrency::from(' usd ')->value());
    }

    public function test_contact_trims_limits_and_normalizes_email(): void
    {
        $contact = ListingContact::from(
            name: '  Иван Иванов  ',
            phone: '  +7 999 000-00-00  ',
            email: '  USER@EXAMPLE.COM  ',
        );

        $this->assertSame([
            'contact_name'  => 'Иван Иванов',
            'contact_phone' => '+7 999 000-00-00',
            'contact_email' => 'user@example.com',
        ], $contact->toPersistence());
    }
}
