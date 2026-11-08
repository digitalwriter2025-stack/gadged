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
            ['Mobile Deal', 'Buy Mobile & Get Exciting Discount with Free Delivery!', 'Online', true, ['phone', 'mobile', 'smartphone'], null],
            ['Triple Desktop Offer', 'Buy Desktop PC & Get Exciting Discount & Gifts!', 'All Outlet', false, ['desktop'], null],
            ['Build Your PC with MSI Offer', 'Buy Selected MSI Products and Get Amazing Gifts', 'All Outlet', false, ['component', 'graphics'], 'images/banners/msi-build-your-pc.webp'],
            ['Logitech MX Series Assured Gift Offer', 'Buy Selected Logitech products and Get Amazing Gifts', 'All Outlet', false, ['accessories'], 'images/banners/logitech-mx-offer.webp'],
            ['Smartwatch & Earbuds Deal', 'Grab smartwatches and earbuds with special mobile deal prices!', 'Online', true, ['watch', 'earbud', 'gadget'], null],
            ['Camera Deal', 'Buy camera and camera lens and get exciting discount and free delivery!', 'All Outlet', false, ['camera'], null],
            ['Monitor Mega Deal', 'Upgrade your display and enjoy exciting discounts!', 'Online', false, ['monitor'], null],
            ['Air Conditioner Deal', 'Buy AC & Enjoy Exciting Discount with Free Delivery!', 'All Outlet', false, ['air conditioner', 'appliance'], null],
            ['TV Deal', 'Buy Smart TV & Get Exciting Discount!', 'All Outlet', false, ['tv', 'television'], null],
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
