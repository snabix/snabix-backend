<?php

declare(strict_types=1);

namespace Tests\Unit\Auth\Domain\Services;

use App\Auth\Domain\Services\UserNameFormatter;
use PHPUnit\Framework\TestCase;

class UserNameFormatterTest extends TestCase
{
    public function test_full_name_uses_only_real_name_parts(): void
    {
        $this->assertSame('Иван Петров', UserNameFormatter::fullName(' Иван ', ' Петров '));
        $this->assertSame('Иван', UserNameFormatter::fullName('Иван', null));
        $this->assertNull(UserNameFormatter::fullName(null, null));
    }

    public function test_account_label_falls_back_to_email(): void
    {
        $this->assertSame(
            'unnamed@example.com',
            UserNameFormatter::accountLabel(null, null, 'unnamed@example.com'),
        );
    }
}
