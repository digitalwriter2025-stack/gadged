<?php

namespace App\Support;

/**
 * Bangladeshi mobile numbers, stored in the local 11-digit form (01XXXXXXXXX).
 */
class PhoneNumber
{
    /**
     * Validation pattern for a normalized number.
     */
    public const PATTERN = '/^01[3-9]\d{8}$/';

    /**
     * Strips spaces, dashes and brackets and the +88 / 88 country prefix.
     */
    public static function normalize(?string $phone): string
    {
        $phone = preg_replace('/[\s\-()]/', '', (string) $phone);

        return preg_replace('/^\+?88(?=01)/', '', $phone);
    }
}
