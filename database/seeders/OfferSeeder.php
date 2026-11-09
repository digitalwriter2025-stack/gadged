<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo offer campaigns modelled on the reference Offers page. Each offer is
 * linked to published products from its matching categories.
 */
class OfferSeeder extends Seeder
{
    public function run(): void
    {
        if (Offer::query()->exists()) {
            return;
        }

        /** @var list<array{0: string, 1: string, 2: string, 3: bool, 4: list<string>, 5: ?string}> $offers title, summary, availability, mobile deal, category keywords, poster */
        $offers = [
            ['Mobile Deal', 'Buy Mobile & Get Exciting Discount with Free Delivery!', 'Online', true, ['phone'], null],
            ['Smartwatch & Earbuds Deal', 'Grab smartwatches and earbuds with special mobile deal prices!', 'Online', true, ['watch', 'earbud'], null],
            ['Power Bank & Charger Deal', 'Stay charged for less: save on power banks, chargers and cables!', 'Online', false, ['powerbank', 'charger'], null],
            ['Speaker & Headphone Deal', 'Buy speakers and headphones and get exciting discounts!', 'All Outlet', false, ['speaker', 'headphone'], null],
        ];

        foreach ($offers as $index => [$title, $summary, $availability, $isMobileDeal, $keywords, $poster]) {
            $offer = Offer::query()->create([
                'title' => $title,
                'slug' => Str::slug($title),
                'summary' => $summary,
                'description' => "{$summary}\n\nOffer valid while stock lasts. Terms and conditions apply.",
                'availability' => $availability,
                'is_mobile_deal' => $isMobileDeal,
                'starts_at' => now()->subDays($index + 1)->startOfDay(),
                'ends_at' => now()->addDays(11)->endOfDay(),
                'sort_order' => $index,
            ]);

            if ($poster !== null && is_file(public_path($poster))) {
                $offer->addMedia(public_path($poster))->preservingOriginal()->toMediaCollection('poster');
            }

            $offer->products()->attach($this->productIdsFor($keywords));
        }
    }

    /**
     * @param  list<string>  $keywords
     * @return list<int>
     */
    private function productIdsFor(array $keywords): array
    {
        $categoryIds = Category::query()
            ->where(fn ($query) => collect($keywords)->each(fn (string $keyword) => $query->orWhere('name', 'like', "%{$keyword}%")))
            ->pluck('id');

        return Product::query()->published()->whereIn('category_id', $categoryIds)->limit(12)->pluck('id')->all();
    }
}
