<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true)).' Deal';

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(8),
            'description' => fake()->paragraph(),
            'availability' => 'Online',
            'is_mobile_deal' => false,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->addDays(10),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => ['starts_at' => now()->subDays(20), 'ends_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
