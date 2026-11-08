<?php

namespace App\Services;

use App\Enums\PcBuilderComponent;
use App\Enums\StockStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Keeps the shopper's PC build in the session as component => product id,
 * and only accepts published, orderable products from the component's category.
 */
class PcBuilderService
{
    public const SESSION_KEY = 'pc_builder';

    /**
     * @var array<string, ?Category>
     */
    private array $categories = [];

    public function __construct(private readonly Session $session) {}

    /**
     * @return array<string, int> component value => product id
     */
    public function selections(): array
    {
        $stored = $this->session->get(self::SESSION_KEY, []);

        return collect(is_array($stored) ? $stored : [])
            ->filter(fn (mixed $productId, mixed $component): bool => PcBuilderComponent::tryFrom((string) $component) !== null && is_int($productId))
            ->all();
    }

    /**
     * Selected products keyed by component value; stale or unpublished ids are dropped.
     *
     * @return Collection<string, Product>
     */
    public function selectedProducts(): Collection
    {
        $selections = $this->selections();

        if ($selections === []) {
            return collect();
        }

        $products = Product::query()->published()->forCard()
            ->whereKey(array_values($selections))
            ->get()
            ->keyBy('id');

        return collect($selections)
            ->map(fn (int $productId): ?Product => $products->get($productId))
            ->filter();
    }

    /**
     * The catalog category a component draws products from, if the store has one.
     */
    public function category(PcBuilderComponent $component): ?Category
    {
        return $this->categories[$component->value] ??= Category::query()
            ->active()
            ->whereIn('slug', $component->categorySlugs())
            ->get()
            ->sortBy(fn (Category $category): int|false => array_search($category->slug, $component->categorySlugs(), true))
            ->first();
    }

    /**
     * Published products that can fill the component slot.
     *
     * @return Builder<Product>
     */
    public function candidates(PcBuilderComponent $component): Builder
    {
        $category = $this->category($component);

        return Product::query()
            ->published()
            ->when(
                $category !== null,
                fn (Builder $query) => $query->inCategories($category->subtreeIds()),
                fn (Builder $query) => $query->whereRaw('1 = 0'),
            );
    }

    /**
     * Put a product in the slot. Returns false when it does not belong there.
     */
    public function choose(PcBuilderComponent $component, int $productId): bool
    {
        $isAvailable = $this->candidates($component)
            ->whereKey($productId)
            ->whereIn('stock_status', [StockStatus::InStock, StockStatus::PreOrder])
            ->exists();

        if (! $isAvailable) {
            return false;
        }

        $this->session->put(self::SESSION_KEY, [...$this->selections(), $component->value => $productId]);

        return true;
    }

    public function remove(PcBuilderComponent $component): void
    {
        $selections = $this->selections();
        unset($selections[$component->value]);

        $this->session->put(self::SESSION_KEY, $selections);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
    }
}
