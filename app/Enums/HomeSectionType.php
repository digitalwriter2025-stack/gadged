<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Kind of block rendered on the homepage.
 */
enum HomeSectionType: string implements HasColor, HasDescription, HasIcon, HasLabel
{
    case Hero = 'hero';
    case ServiceHighlights = 'service_highlights';
    case FeaturedCategories = 'featured_categories';
    case FlashSale = 'flash_sale';
    case ProductCollection = 'product_collection';
    case FeaturedBrands = 'featured_brands';
    case PromoBanners = 'promo_banners';
    case Showcase = 'showcase';
    case ProductGrid = 'product_grid';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hero => 'Hero banners',
            self::ServiceHighlights => 'Service highlights',
            self::FeaturedCategories => 'Featured categories',
            self::FlashSale => 'Flash sale',
            self::ProductCollection => 'Product collection',
            self::FeaturedBrands => 'Featured brands',
            self::PromoBanners => 'Promotional banners',
            self::Showcase => 'Showcase panel',
            self::ProductGrid => 'Product grid',
        };
    }

    /**
     * Badge and icon colour on the Home Sections list, grouping blocks by kind.
     */
    public function getColor(): string
    {
        return match ($this) {
            self::Showcase, self::FlashSale => 'primary',
            self::ProductCollection, self::ProductGrid => 'info',
            self::FeaturedCategories, self::FeaturedBrands => 'success',
            self::Hero, self::ServiceHighlights, self::PromoBanners => 'gray',
        };
    }

    /**
     * Icon beside the block on the Home Sections list, echoing the sidebar where it fits.
     */
    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Hero => Heroicon::OutlinedPhoto,
            self::ServiceHighlights => Heroicon::OutlinedSparkles,
            self::FeaturedCategories => Heroicon::OutlinedFolder,
            self::FlashSale => Heroicon::OutlinedBolt,
            self::ProductCollection => Heroicon::OutlinedShoppingBag,
            self::FeaturedBrands => Heroicon::OutlinedTag,
            self::PromoBanners => Heroicon::OutlinedMegaphone,
            self::Showcase => Heroicon::OutlinedSwatch,
            self::ProductGrid => Heroicon::OutlinedSquares2x2,
        };
    }

    /**
     * What the block looks like on the homepage, in words a shop owner recognises.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::Hero => 'The big slider at the very top, with two small banners beside it.',
            self::ServiceHighlights => 'A strip of trust badges, such as "Cash on delivery".',
            self::FeaturedCategories => 'Round category icons customers tap to browse.',
            self::FlashSale => 'A countdown with the products of the running flash sale.',
            self::ProductCollection => 'A heading with a sliding row of product cards.',
            self::FeaturedBrands => 'A row of brand logos.',
            self::PromoBanners => 'Wide promotional banner images.',
            self::Showcase => 'A coloured panel with a big heading, beside a product slider.',
            self::ProductGrid => 'Product cards in a grid with a "Load more" button.',
        };
    }

    /**
     * Whether the block shows the section heading and subtitle.
     */
    public function hasHeading(): bool
    {
        return ! in_array($this, [self::Hero, self::ServiceHighlights, self::PromoBanners], true);
    }

    /**
     * Whether the admin chooses which products the block lists.
     */
    public function listsProducts(): bool
    {
        return in_array($this, [self::ProductCollection, self::Showcase, self::ProductGrid], true);
    }
}
