<?php

namespace App\Support\Storefront;

use App\Enums\PcBuilderComponent;
use App\Models\Product;
use App\Services\PcBuilderService;
use App\Support\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Data for the PC builder page and its per-component "Choose" listing.
 *
 * @phpstan-type Slot array{
 *     key: string, label: string, icon: string, required: bool, core: bool,
 *     choose_url: string, remove_url: string,
 *     product: ?array{id: int, name: string, url: string, image: string, price: string}
 * }
 */
class PcBuilderPresenter
{
    public const PER_PAGE = 20;

    /**
     * @var array<string, string>
     */
    public const SORTS = [
        'price_asc' => 'Price (Low > High)',
        'price_desc' => 'Price (High > Low)',
        'name' => 'Name (A - Z)',
    ];

    public function __construct(
        private readonly PcBuilderService $builder,
        private readonly ProductCardPresenter $cards,
    ) {}

    /**
     * @return array{
     *     groups: array{core: list<Slot>, peripherals: list<Slot>},
     *     total: string,
     *     itemCount: int,
     *     wattage: int,
     *     missingRequired: list<string>,
     *     breadcrumbs: list<array{label: string, url: ?string}>,
     *     seo: array{title: string, description: string}
     * }
     */
    public function present(): array
    {
        $selected = $this->builder->selectedProducts();

        $slots = collect(PcBuilderComponent::cases())->map(fn (PcBuilderComponent $component): array => [
            'key' => $component->value,
            'label' => $component->label(),
            'icon' => $component->icon(),
            'required' => $component->isRequired(),
            'core' => $component->isCore(),
            'choose_url' => route('store.pc-builder.choose', $component),
            'remove_url' => route('store.pc-builder.remove', $component),
            'product' => ($product = $selected->get($component->value)) === null ? null : [
                'id' => $product->id,
                'name' => $product->name,
                'url' => route('store.products.show', $product->slug),
                'image' => $this->cards->image($product, 'thumb'),
                'price' => Money::format($product->min_price),
            ],
        ]);

        $title = 'PC Builder - Build Your Own Computer - '.config('app.name');

        return [
            'groups' => [
                'core' => $slots->where('core', true)->values()->all(),
                'peripherals' => $slots->where('core', false)->values()->all(),
            ],
            'total' => Money::format(Money::add(...$selected->pluck('min_price')->all())),
            'itemCount' => $selected->count(),
            'wattage' => $selected->keys()->sum(fn (string $key): int => PcBuilderComponent::from($key)->estimatedWattage()),
            'missingRequired' => collect(PcBuilderComponent::cases())
                ->filter(fn (PcBuilderComponent $component): bool => $component->isRequired() && ! $selected->has($component->value))
                ->map(fn (PcBuilderComponent $component): string => $component->label())
                ->values()
                ->all(),
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => 'PC Builder', 'url' => null],
            ],
            'seo' => [
                'title' => $title,
                'description' => 'Pick a processor, motherboard, RAM, storage, graphics card and more, and see the total price of your custom PC at '.config('app.name').'.',
            ],
        ];
    }

    /**
     * @return array{
     *     part: array{key: string, label: string},
     *     products: LengthAwarePaginator<int, array<string, mixed>>,
     *     selectedId: ?int,
     *     sort: string,
     *     sorts: array<string, string>,
     *     breadcrumbs: list<array{label: string, url: ?string}>,
     *     seo: array{title: string, description: string}
     * }
     */
    public function presentChoices(PcBuilderComponent $component, string $sort): array
    {
        $query = $this->builder->candidates($component)->forCard()->with(ProductCardPresenter::relations());

        match ($sort) {
            'price_desc' => $query->orderByDesc('min_price'),
            'name' => $query->orderBy('name'),
            default => $query->orderBy('min_price'),
        };

        $products = $query->orderBy('products.id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(fn (Product $product): array => $this->cards->present($product));

        return [
            'part' => ['key' => $component->value, 'label' => $component->label()],
            'products' => $products,
            'selectedId' => $this->builder->selections()[$component->value] ?? null,
            'sort' => $sort,
            'sorts' => self::SORTS,
            'breadcrumbs' => [
                ['label' => 'Home', 'url' => route('store.home')],
                ['label' => 'PC Builder', 'url' => route('store.pc-builder.show')],
                ['label' => "Choose {$component->label()}", 'url' => null],
            ],
            'seo' => [
                'title' => "Choose {$component->label()} - PC Builder",
                'description' => "Choose a {$component->label()} for your custom PC build.",
            ],
        ];
    }
}
