<?php

namespace Database\Factories;

use App\Enums\CouponScope;
use App\Enums\CouponType;
use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'SAVE'.strtoupper(Str::random(6)),
            'type' => CouponType::Fixed,
            'value' => '100.00',
            'scope' => CouponScope::All,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }

    public function percent(string $value): static
    {
        return $this->state(fn (array $attributes) => ['type' => CouponType::Percent, 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => ['ends_at' => now()->subMinute()]);
    }
}
