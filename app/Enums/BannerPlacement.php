<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a banner is rendered on the storefront.
 */
enum BannerPlacement: string implements HasDescription, HasLabel
{
    case HeroMain = 'hero_main';
    case HeroSideTop = 'hero_side_top';
    case HeroSideBottom = 'hero_side_bottom';
    case Promo = 'promo';
    case CategoryTop = 'category_top';

    public function getLabel(): string
    {
        return match ($this) {
            self::HeroMain => 'Hero slider',
            self::HeroSideTop => 'Hero side (top)',
            self::HeroSideBottom => 'Hero side (bottom)',
            self::Promo => 'Promotional block',
            self::CategoryTop => 'Category page top',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::HeroMain => 'The large rotating slider at the top of the homepage.',
            self::HeroSideTop => 'The small banner beside the slider, upper slot.',
            self::HeroSideBottom => 'The small banner beside the slider, lower slot.',
            self::Promo => 'A wide banner in the homepage promo strip.',
            self::CategoryTop => 'A wide banner above the products on category pages.',
        };
    }

    /**
     * The size the storefront draws this slot at, e.g. "1672 × 941 px".
     */
    public function recommendedImageSize(): ?string
    {
        return match ($this) {
            self::HeroMain => '1672 × 941 px',
            self::HeroSideTop, self::HeroSideBottom => '418 × 216 px',
            self::Promo => '1600 × 603 px',
            self::CategoryTop => null,
        };
    }

    /**
     * Whether phones get the separate mobile artwork; the side banners always show the desktop one.
     */
    public function usesMobileImage(): bool
    {
        return match ($this) {
            self::HeroSideTop, self::HeroSideBottom => false,
            default => true,
        };
    }
}
