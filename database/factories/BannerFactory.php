<?php

namespace Database\Factories;

use App\Enums\BannerPlacement;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'placement' => BannerPlacement::HeroMain,
            'title' => fake()->sentence(3),
            'url' => '/',
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
