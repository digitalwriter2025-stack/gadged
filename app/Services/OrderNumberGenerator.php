<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Human-friendly, gap-tolerant order numbers such as "GD260917-0042".
 *
 * The daily counter is incremented with a single atomic upsert
 * (LAST_INSERT_ID(expr) trick), so concurrent checkouts never collide.
 */
class OrderNumberGenerator
{
    public function __construct(private readonly SettingsService $settings) {}

    public function next(): string
    {
        $today = now(config('app.display_timezone'));

        DB::statement(
            'INSERT INTO order_sequences (`date`, last_number) VALUES (?, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$today->toDateString()],
        );

        $number = (int) DB::selectOne('SELECT LAST_INSERT_ID() AS n')->n;
        $prefix = strtoupper((string) $this->settings->get('orders.number_prefix', 'GD'));

        return sprintf('%s%s-%s', $prefix, $today->format('ymd'), str_pad((string) $number, 4, '0', STR_PAD_LEFT));
    }
}
