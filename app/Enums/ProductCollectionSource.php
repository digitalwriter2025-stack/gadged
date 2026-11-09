<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Where a homepage product collection takes its products from.
 */
enum ProductCollectionSource: string implements HasDescription, HasLabel
{
    case Latest = 'latest';
    case BestSellers = 'best_sellers';
    case Featured = 'featured';
    case Trending = 'trending';
    case Combos = 'combos';
    case Category = 'category';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Latest => 'Latest products',
            self::BestSellers => 'Best sellers',
            self::Featured => 'Featured products',
            self::Trending => 'Trending products',
            self::Combos => 'Combo deals',
            self::Category => 'From a category',
            self::Manual => 'Hand-picked',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Latest => 'Newest products first. Fills itself.',
            self::BestSellers => 'The products that sell the most.',
            self::Featured => 'Products switched to "Featured" on their edit page.',
            self::Trending => 'Products switched to "Trending" on their edit page.',
            self::Combos => 'Combo and bundle products.',
            self::Category => 'Every product of one category you choose.',
            self::Manual => 'You pick each product and its order yourself.',
        };
    }
}
