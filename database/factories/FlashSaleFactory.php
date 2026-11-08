<?php

namespace Database\Factories;

use App\Models\FlashSale;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FlashSale>
 */
class FlashSaleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(2, true)).' Deals';

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDay(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => ['starts_at' => now()->addHour(), 'ends_at' => now()->addDay()]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => ['starts_at' => now()->subDay(), 'ends_at' => now()->subMinute()]);
    }
}
