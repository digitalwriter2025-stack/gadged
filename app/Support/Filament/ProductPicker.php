<?php

declare(strict_types=1);

namespace App\Support\Filament;

use App\Models\Product;
use App\Support\Storefront\ProductCardPresenter;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

/**
 * A searchable select of published products that shows each product's photo
 * beside its name, both in the dropdown and on the picked row.
 *
 * Opening the dropdown lists the first products by name; typing searches all
 * of them on the server by name or SKU. The browser-side search is not used
 * because it would also match the image markup inside each label.
 */
final class ProductPicker
{
    private const OPTIONS_LIMIT = 50;

    public static function make(string $name): Select
    {
        return Select::make($name)
            ->options(fn (): array => self::labels(self::query()->orderBy('name')->limit(self::OPTIONS_LIMIT)))
            ->getSearchResultsUsing(fn (string $search): array => self::labels(self::search($search)))
            ->getOptionLabelUsing(fn (mixed $value): ?string => self::labels(self::query()->whereKey($value))[$value] ?? null)
            ->optionsLimit(self::OPTIONS_LIMIT)
            ->searchable()
            ->allowHtml();
    }

    /**
     * @return Builder<Product>
     */
    private static function query(): Builder
    {
        return Product::query()
            ->published()
            ->select(['id', 'name'])
            ->with(['media' => fn ($media) => $media->where('collection_name', 'main')]);
    }

    /**
     * @return Builder<Product>
     */
    private static function search(string $search): Builder
    {
        $pattern = '%'.addcslashes(mb_substr(trim($search), 0, 100), '\%_').'%';

        return self::query()
            ->where(fn (Builder $query) => $query->where('name', 'like', $pattern)->orWhere('sku', 'like', $pattern))
            ->orderBy('name')
            ->limit(self::OPTIONS_LIMIT);
    }

    /**
     * @param  Builder<Product>  $query
     * @return array<int, string> product id => label HTML
     */
    private static function labels(Builder $query): array
    {
        $cards = app(ProductCardPresenter::class);

        return $query->get()
            ->mapWithKeys(fn (Product $product): array => [
                $product->id => sprintf(
                    '<span class="gx-product-option"><img src="%s" alt="" loading="lazy"><span>%s</span></span>',
                    e($cards->image($product, 'thumb')),
                    e($product->name),
                ),
            ])
            ->all();
    }
}
