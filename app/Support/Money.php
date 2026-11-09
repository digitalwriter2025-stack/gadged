<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Decimal-safe money arithmetic on numeric strings (2 decimal places, bcmath).
 * Amounts are stored as DECIMAL(12,2) and must never pass through floats.
 */
final class Money
{
    public const SCALE = 2;

    public static function of(string|int|float|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '0.00';
        }

        if (is_float($amount)) {
            $amount = number_format($amount, self::SCALE, '.', '');
        }

        $amount = trim((string) $amount);

        if (! is_numeric($amount)) {
            throw new InvalidArgumentException("Invalid money amount [{$amount}].");
        }

        return self::round($amount);
    }

    public static function add(string|int|null ...$amounts): string
    {
        $total = '0';

        foreach ($amounts as $amount) {
            $total = bcadd($total, self::of($amount), self::SCALE);
        }

        return $total;
    }

    public static function sub(string|int|null $amount, string|int|null $subtract): string
    {
        return bcsub(self::of($amount), self::of($subtract), self::SCALE);
    }

    public static function mul(string|int|null $amount, string|int $multiplier): string
    {
        return self::round(bcmul(self::of($amount), (string) $multiplier, self::SCALE + 4));
    }

    /**
     * The given percentage of an amount, rounded half-up to 2 decimals.
     */
    public static function percentOf(string|int|null $amount, string|int $percent): string
    {
        return self::round(bcdiv(bcmul(self::of($amount), (string) $percent, 6), '100', 6));
    }

    /**
     * Whole-number percentage that $part is of $whole (for "Save 12%" badges).
     */
    public static function percentage(string|int|null $part, string|int|null $whole): int
    {
        if (self::isZero($whole)) {
            return 0;
        }

        return (int) bcdiv(bcmul(self::of($part), '100', 6), self::of($whole), 0);
    }

    public static function min(string|int|null $a, string|int|null $b): string
    {
        return self::compare($a, $b) <= 0 ? self::of($a) : self::of($b);
    }

    public static function max(string|int|null $a, string|int|null $b): string
    {
        return self::compare($a, $b) >= 0 ? self::of($a) : self::of($b);
    }

    /**
     * Never lets an amount go below zero.
     */
    public static function floor0(string|int|null $amount): string
    {
        return self::max($amount, '0');
    }

    public static function compare(string|int|null $a, string|int|null $b): int
    {
        return bccomp(self::of($a), self::of($b), self::SCALE);
    }

    public static function isZero(string|int|null $amount): bool
    {
        return self::compare($amount, '0') === 0;
    }

    public static function isPositive(string|int|null $amount): bool
    {
        return self::compare($amount, '0') === 1;
    }

    /**
     * Formats for display, e.g. "৳12,500" (decimals only when non-zero), with
     * Bangla digits ("৳১২,৫০০") while the storefront is in Bangla.
     */
    public static function format(string|int|null $amount, string $symbol = '৳'): string
    {
        $amount = self::of($amount);
        $decimals = str_ends_with($amount, '.00') ? 0 : self::SCALE;

        return Localization::digits($symbol.number_format((float) $amount, $decimals));
    }

    /**
     * Half-up rounding to SCALE decimals on an arbitrary-precision numeric string.
     */
    private static function round(string $amount): string
    {
        $offset = '0.'.str_repeat('0', self::SCALE).'5';

        return str_starts_with($amount, '-')
            ? bcsub($amount, $offset, self::SCALE)
            : bcadd($amount, $offset, self::SCALE);
    }
}
