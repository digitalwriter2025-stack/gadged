<?php

namespace Database\Factories;

use App\Enums\LedgerAccount;
use App\Enums\LedgerCategory;
use App\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LedgerEntry>
 */
class LedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => LedgerCategory::OtherExpense,
            'title' => fake()->sentence(3),
            'amount' => '500.00',
            'entry_date' => now(config('app.display_timezone'))->toDateString(),
            'account' => LedgerAccount::Cash,
        ];
    }

    public function category(LedgerCategory $category): static
    {
        return $this->state(fn (array $attributes) => ['category' => $category]);
    }
}
