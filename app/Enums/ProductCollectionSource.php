<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Where a homepage product collection takes its products from.
 */
enum ProductCollectionSource: string implements HasLabel
{
    case Latest = 'latest';
    case BestSellers = 'best_sellers';
    case Featured = 'featured';
    case Category = 'category';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Latest => 'Latest products',
            self::BestSellers => 'Best sellers',
            self::Featured => 'Featured products',
            self::Category => 'From a category',
            self::Manual => 'Hand-picked',
        };
    }
}
