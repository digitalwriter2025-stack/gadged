<?php

namespace App\Support\Storefront;

use App\Enums\PaymentMethod;
use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\FlashSaleProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Turns a product into the plain arrays rendered by store/product.blade.php.
 */
class ProductPagePresenter
{
    private const KEY_FEATURE_COUNT = 4;

    private const RELATED_LIMIT = 8;

    private const REVIEW_LIMIT = 10;

    /**
     * EMI tenures offered in the plan table, capped by shop.emi.max_tenure_months.
     *
     * @var list<int>
     */
    private const EMI_TENURES = [3, 6, 9, 12, 18, 24, 36];

    public function __construct(
        private readonly StoreLayout $layout,
        private readonly ProductSpecifications $specifications,
        private readonly ProductCardPresenter $cards,
    ) {}

    /**
     * @return array{
     *     product: array{id: int, name: string, sku: string, model: ?string, warranty: ?string, brand: ?string, brand_url: ?string, category: ?string, category_url: ?string, stock_label: string, in_stock: bool, purchasable: bool, short_description: ?string, description: ?string, offer: ?string, rating: float, rating_count: int, url: string},
     *     pricing: array{amount: string, price: string, regular_price: ?string, regular_amount: ?string, savings: ?string, emi_monthly: ?string, emi_months: int, emi_plans: list<array{months: int, monthly: string}>},
     *     paymentMethods: list<string>,
     *     faqs: list<array{question: string, answer: string}>,
     *     contact: array{hotline: string, hotline_tel: string, email: string, address: string, whatsapp: string},
     *     images: list<array{large: string, thumb: string, alt: string}>,
     *     breadcrumbs: list<array{label: string, url: ?string}>,
     *     keyFeatures: list<string>,
     *     specificationGroups: list<array{name: string, rows: list<array{label: string, values: list<string>}>}>,
     *     reviews: list<array{author: string, rating: int, title: ?string, body: ?string, date: string, verified: bool}>,
     *     relatedProducts: list<array{name: string, url: string, image: string, price: string, regular_price: ?string, savings: ?string}>,
     *     seo: array{title: string, description: string, heading: string}
     * }
     */
    public function present(Product $product): array
    {
        $product->load([
            'brand:id,name,slug',
            'category:id,name,slug,path',
            'media' => fn ($query) => $query->whereIn('collection_name', ['main', 'gallery']),
            'activeVariants',
            'specificationValues.specification.group',
            'specificationValues.option',
        ]);

        $specificationGroups = $this->specificationGroups($product);

        return [
            'product' => $this->details($product),
            'pricing' => $this->pricing($product),
            'paymentMethods' => array_map(fn (PaymentMethod $method): string => $method->getLabel(), PaymentMethod::cases()),
            'faqs' => $this->faqs($product),
            'contact' => $this->layout->contact(),
            'images' => $this->images($product),
            'breadcrumbs' => $this->breadcrumbs($product),
            'keyFeatures' => $this->specifications->keyFeatures($specificationGroups, self::KEY_FEATURE_COUNT),
            'specificationGroups' => $specificationGroups,
            'reviews' => $this->reviews($product),
            'relatedProducts' => $this->relatedProducts($product),
            'seo' => $this->seo($product),
        ];
    }

    /**
     * @return array{id: int, name: string, sku: string, model: ?string, warranty: ?string, brand: ?string, brand_url: ?string, category: ?string, category_url: ?string, stock_label: string, in_stock: bool, purchasable: bool, short_description: ?string, description: ?string, offer: ?string, rating: float, rating_count: int, url: string}
     */
    private function details(Product $product): array
    {
        $description = filled($product->description) ? Str::sanitizeHtml($product->description) : null;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'model' => $product->model,
            'warranty' => $product->warranty,
            'brand' => $product->brand?->name,
            'brand_url' => $this->brandUrl($product),
            'category' => $product->category?->name,
            'category_url' => $product->category ? route('store.categories.show', $product->category->slug) : null,
            'stock_label' => $product->stock_status->getLabel(),
            'in_stock' => $product->stock_status === StockStatus::InStock,
            'purchasable' => $product->isPurchasable(),
            'short_description' => $product->short_description,
            'description' => $description,
            'offer' => filled($product->offer_text) ? $product->offer_text : null,
            'rating' => round((float) $product->rating_avg, 1),
            'rating_count' => $product->rating_count,
            'url' => route('store.products.show', $product->slug),
        ];
    }

    /**
     * @return array{amount: string, price: string, regular_price: ?string, regular_amount: ?string, savings: ?string, emi_monthly: ?string, emi_months: int, emi_plans: list<array{months: int, monthly: string}>}
     */
    private function pricing(Product $product): array
    {
        $variant = $this->displayVariant($product);
        $months = (int) config('shop.emi.max_tenure_months');

        if ($variant === null) {
            return [
                'amount' => Money::of($product->min_price),
                'price' => Money::format($product->min_price),
                'regular_price' => null,
                'regular_amount' => null,
                'savings' => null,
                'emi_monthly' => null,
                'emi_months' => $months,
                'emi_plans' => [],
            ];
        }

        $price = Money::min($variant->currentPrice(), $this->flashSalePrice($product, $variant) ?? $variant->price);
        $savings = Money::sub($variant->price, $price);
        $hasSavings = Money::isPositive($savings);

        return [
            'amount' => $price,
            'price' => Money::format($price),
            'regular_price' => $hasSavings ? Money::format($variant->price) : null,
            'regular_amount' => $hasSavings ? Money::of($variant->price) : null,
            'savings' => $hasSavings ? Money::format($savings) : null,
            'emi_monthly' => $this->emiMonthly($price, $months),
            'emi_months' => $months,
            'emi_plans' => collect(self::EMI_TENURES)
                ->filter(fn (int $tenure): bool => $tenure <= $months)
                ->map(fn (int $tenure): array => ['months' => $tenure, 'monthly' => $this->emiMonthly($price, $tenure)])
                ->filter(fn (array $plan): bool => $plan['monthly'] !== null)
                ->values()
                ->all(),
        ];
    }

    /**
     * The variant whose price is shown: the default one, otherwise the cheapest active variant.
     */
    private function displayVariant(Product $product): ?ProductVariant
    {
        $variants = $product->activeVariants;

        return $variants->firstWhere('is_default', true)
            ?? $variants->sortBy(fn (ProductVariant $variant): float => (float) $variant->currentPrice())->first();
    }

    private function flashSalePrice(Product $product, ProductVariant $variant): ?string
    {
        $item = FlashSaleProduct::query()
            ->where('product_id', $product->id)
            ->where(fn (Builder $query) => $query->whereNull('product_variant_id')->orWhere('product_variant_id', $variant->id))
            ->whereHas('flashSale', fn (Builder $query) => $query->running())
            ->first();

        if ($item === null || $item->isSoldOut()) {
            return null;
        }

        return $item->priceFrom(Money::of($variant->price));
    }

    /**
     * Lowest monthly instalment, rounded up to a whole taka.
     */
    private function emiMonthly(string $price, int $months): ?string
    {
        if ($months < 1 || Money::compare($price, config('shop.emi.min_price')) < 0) {
            return null;
        }

        $monthly = bcdiv($price, (string) $months, 0);

        if (bccomp(bcmul($monthly, (string) $months, 2), $price, 2) < 0) {
            $monthly = bcadd($monthly, '1', 0);
        }

        return Money::format($monthly);
    }

    /**
     * @return list<array{large: string, thumb: string, alt: string}>
     */
    private function images(Product $product): array
    {
        $media = $product->media
            ->sortBy(fn (Media $item): string => ($item->collection_name === 'main' ? '0' : '1').str_pad((string) $item->order_column, 10, '0', STR_PAD_LEFT))
            ->values();

        $images = $media->map(fn (Media $item, int $index): array => [
            'large' => $item->getAvailableUrl(['large', 'medium']),
            'thumb' => $item->getAvailableUrl(['thumb']),
            'alt' => $index === 0 ? $product->name : "{$product->name} image ".($index + 1),
        ])->all();

        if ($images !== []) {
            return $images;
        }

        $fallback = asset(ProductCardPresenter::PLACEHOLDER_IMAGE);

        return [['large' => $fallback, 'thumb' => $fallback, 'alt' => $product->name]];
    }

    /**
     * @return list<array{label: string, url: ?string}>
     */
    private function breadcrumbs(Product $product): array
    {
        $crumbs = [['label' => 'Home', 'url' => route('store.home')]];

        if ($product->category !== null) {
            $ancestors = Category::query()
                ->whereKey($product->category->ancestorIds())
                ->orderBy('depth')
                ->get(['id', 'name', 'slug']);

            foreach ($ancestors as $ancestor) {
                $crumbs[] = ['label' => $ancestor->name, 'url' => route('store.categories.show', $ancestor->slug)];
            }

            $crumbs[] = ['label' => $product->category->name, 'url' => route('store.categories.show', $product->category->slug)];
        }

        if ($product->brand !== null) {
            $crumbs[] = ['label' => $product->brand->name, 'url' => $this->brandUrl($product)];
        }

        $crumbs[] = ['label' => $product->name, 'url' => null];

        return $crumbs;
    }

    /**
     * The brand's products inside the primary category, until brand pages exist.
     */
    private function brandUrl(Product $product): ?string
    {
        if ($product->brand === null || $product->category === null) {
            return null;
        }

        return route('store.categories.show', ['category' => $product->category->slug, 'brand' => [$product->brand->slug]]);
    }

    /**
     * Specification table rows, with a warranty row added when no specification covers it.
     *
     * @return list<array{name: string, rows: list<array{label: string, values: list<string>}>}>
     */
    private function specificationGroups(Product $product): array
    {
        $groups = $this->specifications->groups($product);

        $hasWarrantySpec = collect($groups)->flatMap(fn (array $group): array => $group['rows'])
            ->contains(fn (array $row): bool => $this->specifications->isWarranty($row['label']));

        if (filled($product->warranty) && ! $hasWarrantySpec) {
            $groups[] = ['name' => 'Warranty Information', 'rows' => [['label' => 'Warranty', 'values' => [$product->warranty]]]];
        }

        return $groups;
    }

    /**
     * @return list<array{question: string, answer: string}>
     */
    private function faqs(Product $product): array
    {
        return collect($product->faqs ?? [])
            ->filter(fn (mixed $faq): bool => is_array($faq) && filled($faq['question'] ?? null) && filled($faq['answer'] ?? null))
            ->map(fn (array $faq): array => ['question' => trim((string) $faq['question']), 'answer' => trim((string) $faq['answer'])])
            ->values()
            ->all();
    }

    /**
     * @return list<array{author: string, rating: int, title: ?string, body: ?string, date: string, verified: bool}>
     */
    private function reviews(Product $product): array
    {
        return $product->approvedReviews()
            ->with('user:id,name')
            ->latest()
            ->limit(self::REVIEW_LIMIT)
            ->get()
            ->map(fn (Review $review): array => [
                'author' => $review->user?->name ?? 'Customer',
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'date' => $review->created_at->format('d M Y'),
                'verified' => $review->is_verified_purchase,
            ])
            ->all();
    }

    /**
     * Published products from the same primary category, best sellers first.
     *
     * @return list<array{name: string, url: string, image: string, price: string, regular_price: ?string, savings: ?string}>
     */
    private function relatedProducts(Product $product): array
    {
        return Product::query()
            ->published()
            ->forCard()
            ->where('products.category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->orderByDesc('products.sales_count')
            ->latest('products.id')
            ->limit(self::RELATED_LIMIT)
            ->get()
            ->map(function (Product $related): array {
                $savings = Money::sub($related->regular_price, $related->min_price);
                $hasSavings = Money::isPositive($savings);

                return [
                    'name' => $related->name,
                    'url' => route('store.products.show', $related->slug),
                    'image' => $this->cards->image($related, 'thumb'),
                    'price' => Money::format($related->min_price),
                    'regular_price' => $hasSavings ? Money::format($related->regular_price) : null,
                    'savings' => $hasSavings ? Money::format($savings) : null,
                ];
            })
            ->all();
    }

    /**
     * @return array{title: string, description: string, heading: string}
     */
    private function seo(Product $product): array
    {
        $year = now()->year;

        return [
            'title' => $product->seo_title ?: "{$product->name} Price in Bangladesh {$year}",
            'description' => $product->seo_description ?: Str::limit((string) ($product->short_description ?: $product->name), 160),
            'heading' => "What is the {$product->name} Price in Bangladesh {$year}?",
        ];
    }
}
