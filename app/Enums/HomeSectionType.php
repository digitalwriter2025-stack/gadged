<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Kind of block rendered on the homepage.
 */
enum HomeSectionType: string implements HasLabel
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
            self::Showcase => 'Showcase (promo panel + products)',
            self::ProductGrid => 'Product grid',
        };
    }
}
