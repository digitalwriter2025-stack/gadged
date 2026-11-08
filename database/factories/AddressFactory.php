<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => 'Home',
            'name' => fake()->name(),
            'phone' => '01'.fake()->numerify('#########'),
            'address_line' => fake()->streetAddress(),
            'area' => fake()->citySuffix(),
            'district_id' => null,
            'is_default' => false,
        ];
    }
}
