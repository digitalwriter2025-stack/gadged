<?php

namespace App\Models;

use App\Enums\HomeSectionType;
use App\Models\Concerns\HasImageConversions;
use App\Models\Concerns\HasTranslations;
use App\Observers\CatalogCacheObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Ordered homepage block. `settings` carries type-specific options such as
 * {"source": "latest", "limit": 12} for a product collection. Showcase
 * sections keep their collage artwork in the "images" media collection.
 */
#[Fillable(['type', 'title', 'subtitle', 'settings', 'is_active', 'sort_order', 'title_bn', 'subtitle_bn'])]
#[ObservedBy(CatalogCacheObserver::class)]
class HomeSection extends Model implements HasMedia
{
    use HasImageConversions, HasTranslations;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['images'], ['card' => 400]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => HomeSectionType::class,
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * @return HasMany<HomeSectionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order');
    }

    /**
     * Swaps the block with the one directly above it on the homepage.
     */
    public function moveUp(): void
    {
        $this->swapWithNeighbour(-1);
    }

    /**
     * Swaps the block with the one directly below it on the homepage.
     */
    public function moveDown(): void
    {
        $this->swapWithNeighbour(1);
    }

    /**
     * Renumbers every block 1, 2, 3… with this one swapped a place, so gaps or
     * repeated numbers typed into the Position field never stop a move.
     */
    private function swapWithNeighbour(int $offset): void
    {
        DB::transaction(function () use ($offset): void {
            $blocks = self::query()->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $from = $blocks->search(fn (self $block): bool => $block->is($this));

            if ($from === false || ! $blocks->has($from + $offset)) {
                return;
            }

            $order = $blocks->all();
            [$order[$from], $order[$from + $offset]] = [$order[$from + $offset], $order[$from]];

            foreach ($order as $index => $block) {
                $block->sort_order = $index + 1;

                if ($block->isDirty('sort_order')) {
                    $block->save();
                }
            }
        });

        $this->refresh();
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
