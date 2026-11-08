<?php

namespace Tests\Feature\Promotions;

use App\Enums\FlashSaleDiscountType;
use App\Models\Banner;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\FlashSaleProduct;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Model-level promotion rules that the checkout services will build on.
 */
class PromotionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_sale_is_running_only_inside_its_window_by_server_time(): void
    {
        $running = FlashSale::factory()->create();
        $upcoming = FlashSale::factory()->upcoming()->create();
        $ended = FlashSale::factory()->ended()->create();
        $disabled = FlashSale::factory()->create(['is_active' => false]);

        $this->assertTrue($running->isRunning());
        $this->assertFalse($upcoming->isRunning());
        $this->assertFalse($ended->isRunning());
        $this->assertFalse($disabled->isRunning());
        $this->assertSame([$running->id], FlashSale::query()->running()->pluck('id')->all());

        $this->travelTo($running->ends_at);
        $this->assertFalse($running->fresh()->isRunning(), 'A sale is over at exactly ends_at.');
        $this->assertSame([], FlashSale::query()->running()->pluck('id')->all());
    }

    public function test_flash_sale_price_calculation(): void
    {
        $item = FlashSaleProduct::factory()->make(['discount_type' => FlashSaleDiscountType::FixedPrice, 'discount_value' => '2949']);
        $this->assertSame('2949.00', $item->priceFrom('3600'));

        $item->discount_type = FlashSaleDiscountType::Amount;
        $item->discount_value = '651';
        $this->assertSame('2949.00', $item->priceFrom('3600'));

        $item->discount_type = FlashSaleDiscountType::Percent;
        $item->discount_value = '10';
        $this->assertSame('3240.00', $item->priceFrom('3600'));

        $item->discount_type = FlashSaleDiscountType::Amount;
        $item->discount_value = '5000';
        $this->assertSame('0.00', $item->priceFrom('3600'), 'Never negative.');

        $item->discount_type = FlashSaleDiscountType::FixedPrice;
        $item->discount_value = '9999';
        $this->assertSame('3600.00', $item->priceFrom('3600'), 'Never above the regular price.');
    }

    public function test_flash_sale_quantity_limit(): void
    {
        $item = FlashSaleProduct::factory()->make(['quantity_limit' => 10]);
        $item->sold_quantity = 10;

        $this->assertSame(0, $item->remainingQuantity());
        $this->assertTrue($item->isSoldOut());
        $this->assertNull(FlashSaleProduct::factory()->make(['quantity_limit' => null])->remainingQuantity());
    }

    public function test_coupon_code_is_normalised_and_schedule_is_checked(): void
    {
        $coupon = Coupon::factory()->create(['code' => '  welcome100 ']);
        $this->assertSame('WELCOME100', $coupon->fresh()->code);
        $this->assertTrue($coupon->isWithinSchedule());
        $this->assertFalse(Coupon::factory()->expired()->create()->isWithinSchedule());

        $limited = Coupon::factory()->create(['usage_limit' => 1]);
        $this->assertTrue($limited->hasUsesLeft());
        $limited->forceFill(['used_count' => 1])->save();
        $this->assertFalse($limited->fresh()->hasUsesLeft());
    }

    public function test_shipping_rate_applies_free_shipping_threshold(): void
    {
        $rate = ShippingRate::query()->create([
            'shipping_zone_id' => ShippingZone::query()->create(['name' => 'Inside Dhaka', 'code' => 'inside'])->id,
            'shipping_method_id' => ShippingMethod::query()->create(['name' => 'Standard', 'code' => 'std'])->id,
            'charge' => '60',
            'free_shipping_threshold' => '5000',
        ]);

        $this->assertSame('60.00', $rate->chargeFor('4999.99'));
        $this->assertSame('0.00', $rate->chargeFor('5000'));
    }

    public function test_banner_visibility_respects_schedule(): void
    {
        $visible = Banner::factory()->create();
        Banner::factory()->create(['starts_at' => now()->addDay()]);
        Banner::factory()->create(['ends_at' => now()->subMinute()]);
        Banner::factory()->create(['is_active' => false]);

        $this->assertSame([$visible->id], Banner::query()->visible()->pluck('id')->all());
    }
}
