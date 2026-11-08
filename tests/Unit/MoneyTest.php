<?php

namespace Tests\Unit;

use App\Support\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_normalises_amounts_to_two_decimals(): void
    {
        $this->assertSame('0.00', Money::of(null));
        $this->assertSame('12.00', Money::of(12));
        $this->assertSame('12.35', Money::of('12.345'));
        $this->assertSame('-12.35', Money::of('-12.345'));
        $this->assertSame('0.30', Money::of(0.1 + 0.2));
    }

    public function test_it_rejects_non_numeric_input(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::of('12abc');
    }

    public function test_addition_and_subtraction_are_exact(): void
    {
        $this->assertSame('0.30', Money::add('0.10', '0.20'));
        $this->assertSame('2949.00', Money::add('1000', '1949', null));
        $this->assertSame('-50.00', Money::sub('100', '150'));
        $this->assertSame('0.00', Money::floor0(Money::sub('100', '150')));
    }

    public function test_multiplication_rounds_half_up(): void
    {
        $this->assertSame('50.00', Money::mul('33.33', '1.5'));
        $this->assertSame('89970.00', Money::mul('29990', 3));
    }

    /**
     * @return array<string, array{string, int, string}>
     */
    public static function percentProvider(): array
    {
        return [
            'ten percent' => ['2949', 10, '294.90'],
            'rounds half up' => ['99.95', 5, '5.00'],
            'zero' => ['500', 0, '0.00'],
        ];
    }

    #[DataProvider('percentProvider')]
    public function test_percent_of(string $amount, int $percent, string $expected): void
    {
        $this->assertSame($expected, Money::percentOf($amount, $percent));
    }

    public function test_percentage_badge_is_whole_number_and_safe_on_zero(): void
    {
        $this->assertSame(15, Money::percentage('651', '4300'));
        $this->assertSame(0, Money::percentage('100', '0'));
    }

    public function test_comparison_helpers(): void
    {
        $this->assertSame('5.00', Money::min('5', '7.5'));
        $this->assertSame('7.50', Money::max('5', '7.5'));
        $this->assertTrue(Money::isZero('0.00'));
        $this->assertTrue(Money::isPositive('0.01'));
        $this->assertFalse(Money::isPositive('-1'));
    }

    public function test_format_hides_zero_decimals(): void
    {
        $this->assertSame('৳12,500', Money::format('12500'));
        $this->assertSame('৳12,500.50', Money::format('12500.5'));
    }
}
