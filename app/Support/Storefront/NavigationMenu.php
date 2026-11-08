<?php

namespace App\Support\Storefront;

use App\Models\Category;
use App\Services\CatalogCache;
use Illuminate\Support\Collection;

/**
 * Builds the header category menu (root categories with two levels of children)
 * from the catalog; order and visibility follow each category's sort_order and
 * show_in_menu flag.
 *
 * @phpstan-type MenuLink array{label: string, url: string, children: list<array{label: string, url: string, children: list<array{label: string, url: string}>}>}
 */
class NavigationMenu
{
    public const MAX_DEPTH = 2;

    /**
     * Root categories that fit on the single-line desktop header bar.
     */
    public const HEADER_LIMIT = 14;

    public function __construct(
        private readonly CatalogCache $cache,
    ) {}

    /**
     * @return list<MenuLink>
     */
    public function links(): array
    {
        return $this->cache->rememberForever('navigation.header', fn (): array => $this->build());
    }

    /**
     * @return list<MenuLink>
     */
    private function build(): array
    {
        $categories = Category::query()
            ->select(['id', 'parent_id', 'name', 'slug', 'depth', 'sort_order'])
            ->active()
            ->where('show_in_menu', true)
            ->where('depth', '<=', self::MAX_DEPTH)
            ->ordered()
            ->get()
            ->groupBy(fn (Category $category): int => (int) $category->parent_id);

        return $this->branch($categories, 0, 0);
    }

    /**
     * @param  Collection<int, Collection<int, Category>>  $categories
     * @return list<array<string, mixed>>
     */
    private function branch(Collection $categories, int $parentId, int $level): array
    {
        return $categories->get($parentId, collect())
            ->map(function (Category $category) use ($categories, $level): array {
                $link = ['label' => $category->name, 'url' => route('store.categories.show', $category->slug)];

                return $level < self::MAX_DEPTH
                    ? [...$link, 'children' => $this->branch($categories, $category->id, $level + 1)]
                    : $link;
            })
            ->values()
            ->all();
    }
}
