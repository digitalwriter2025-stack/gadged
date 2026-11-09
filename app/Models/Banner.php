<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use App\Models\Concerns\HasImageConversions;
use App\Models\Concerns\HasTranslations;
use App\Observers\CatalogCacheObserver;
use App\Support\Localization;
use Carbon\CarbonInterface;
use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Hero slide (placement hero_main) or promotional banner, with separate
 * desktop and mobile artwork and an optional schedule.
 */
#[Fillable([
    'placement', 'title', 'subtitle', 'cta_label', 'url', 'open_in_new_tab', 'alt_text',
    'starts_at', 'ends_at', 'is_active', 'sort_order',
    'title_bn', 'subtitle_bn', 'cta_label_bn', 'alt_text_bn',
])]
#[ObservedBy(CatalogCacheObserver::class)]
class Banner extends Model implements HasMedia
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory, HasImageConversions, HasTranslations;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'open_in_new_tab' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('desktop')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
        $this->addMediaCollection('mobile')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
        // Optional Bangla artwork, for banners with text inside the image.
        $this->addMediaCollection('desktop_bn')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
        $this->addMediaCollection('mobile_bn')->singleFile()->acceptsMimeTypes(self::IMAGE_MIME_TYPES);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addWebpConversions(['desktop', 'desktop_bn'], ['desktop' => 1920], responsive: ['desktop']);
        $this->addWebpConversions(['mobile', 'mobile_bn'], ['mobile' => 828], responsive: ['mobile']);
    }

    /**
     * Banner artwork for the storefront language: the Bangla upload when there is one, else the English image.
     */
    public function imageFor(string $collection): ?Media
    {
        return $this->getFirstMedia(Localization::column($collection)) ?? $this->getFirstMedia($collection);
    }

    public function isVisible(?CarbonInterface $at = null): bool
    {
        $at ??= now();

        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->lte($at))
            && ($this->ends_at === null || $this->ends_at->gt($at));
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query, ?CarbonInterface $at = null): void
    {
        $at ??= now();

        $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', $at))
            ->orderBy('sort_order');
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function placement(Builder $query, BannerPlacement $placement): void
    {
        $query->where('placement', $placement);
    }
}
