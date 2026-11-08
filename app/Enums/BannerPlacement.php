<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a banner is rendered on the storefront.
 */
enum BannerPlacement: string implements HasLabel
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
}
