<?php

namespace Database\Factories;

use App\Enums\ReviewStatus;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'rating' => fake()->numberBetween(3, 5),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'status' => ReviewStatus::Pending,
            'is_verified_purchase' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ReviewStatus::Approved, 'moderated_at' => now()]);
    }
}
