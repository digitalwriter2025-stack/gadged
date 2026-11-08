<?php

namespace Tests\Feature\Orders;

use App\Services\OrderNumberGenerator;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_numbers_are_sequential_per_day_in_the_display_timezone(): void
    {
        // 20:00 UTC on 16 Sep is already 17 Sep in Dhaka (UTC+6).
        $this->travelTo(Carbon::parse('2026-09-16 20:00:00', 'UTC'));
        $generator = app(OrderNumberGenerator::class);

        $this->assertSame('GD260917-0001', $generator->next());
        $this->assertSame('GD260917-0002', $generator->next());

        $this->travelTo(Carbon::parse('2026-09-17 19:00:00', 'UTC'));
        $this->assertSame('GD260918-0001', $generator->next());
    }

    public function test_prefix_comes_from_settings(): void
    {
        app(SettingsService::class)->set('orders.number_prefix', 'tl');

        $this->assertStringStartsWith('TL', app(OrderNumberGenerator::class)->next());
    }

    public function test_many_numbers_are_unique(): void
    {
        $generator = app(OrderNumberGenerator::class);
        $numbers = array_map(fn (): string => $generator->next(), range(1, 50));

        $this->assertCount(50, array_unique($numbers));
    }
}
