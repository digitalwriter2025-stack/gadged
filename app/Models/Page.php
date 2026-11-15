<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Static CMS page (About, Privacy Policy, Warranty Policy, ...).
 */
#[Fillable(['title', 'slug', 'content', 'is_active', 'seo_title', 'seo_description', 'title_bn', 'content_bn', 'seo_title_bn', 'seo_description_bn'])]
#[ObservedBy(CatalogCacheObserver::class)]
class Page extends Model
{
    use HasTranslations;

    /**
     * Footer menu locations a page can be listed under, with their default column titles.
     *
     * @var array<string, string>
     */
    public const FOOTER_LOCATIONS = [
        'footer_information' => 'Information',
        'footer_policies' => 'Policies',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(fn (self $page) => $page->menuItems()->delete());
    }

    /**
     * Menu links (header or footer) pointing at this page.
     *
     * @return MorphMany<MenuItem, $this>
     */
    public function menuItems(): MorphMany
    {
        return $this->morphMany(MenuItem::class, 'linkable');
    }

    /**
     * Footer column this page is linked from, if any.
     */
    public function footerLocation(): ?string
    {
        return $this->menuItems()
            ->whereHas('menu', fn (Builder $menu) => $menu->whereIn('location', array_keys(self::FOOTER_LOCATIONS)))
            ->with('menu')
            ->first()
            ?->menu
            ->location;
    }

    /**
     * Link this page from the given footer column; any other value removes it from the footer.
     */
    public function placeInFooter(?string $location): void
    {
        if (! array_key_exists((string) $location, self::FOOTER_LOCATIONS)) {
            $location = null;
        }

        $existing = $this->menuItems()
            ->whereHas('menu', fn (Builder $menu) => $menu->whereIn('location', array_keys(self::FOOTER_LOCATIONS)))
            ->get();

        $kept = $existing->first(fn (MenuItem $item): bool => $item->menu->location === $location);

        $existing->reject(fn (MenuItem $item): bool => $item->is($kept))->each->delete();

        if ($kept !== null) {
            $kept->update(['label' => $this->title, 'label_bn' => $this->title_bn]);
        }

        if ($location === null || $kept !== null) {
            return;
        }

        $menu = Menu::query()->firstOrCreate(['location' => $location], ['name' => self::FOOTER_LOCATIONS[$location]]);

        $menu->items()->create([
            'label' => $this->title,
            'label_bn' => $this->title_bn,
            'linkable_type' => $this->getMorphClass(),
            'linkable_id' => $this->getKey(),
            'is_active' => true,
            'sort_order' => (int) $menu->items()->max('sort_order') + 1,
        ]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
