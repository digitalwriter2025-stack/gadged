<?php

namespace Database\Factories;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Order header only; tests add items explicitly.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'TEST-'.strtoupper(Str::random(10)),
            'user_id' => null,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'payment_method' => PaymentMethod::CashOnDelivery,
            'source' => OrderSource::Web,
            'customer_name' => fake()->name(),
            'customer_phone' => '01'.fake()->numerify('#########'),
            'shipping_address' => fake()->streetAddress(),
            'shipping_district' => 'Dhaka',
            'subtotal' => '1000.00',
            'shipping_total' => '60.00',
            'grand_total' => '1060.00',
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn (array $attributes) => ['status' => $status]);
    }
}
