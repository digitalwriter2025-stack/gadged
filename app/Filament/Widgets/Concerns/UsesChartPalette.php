<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Concerns;

/**
 * One colour per bar, so each category or product is easy to tell apart. Same hues as the dashboard stat tiles.
 */
trait UsesChartPalette
{
    /**
     * @var list<string>
     */
    private const BAR_PALETTE = [
        '#ff5a36',
        '#3b82f6',
        '#a855f7',
        '#14b8a6',
        '#f59e0b',
        '#ec4899',
        '#10b981',
        '#6366f1',
    ];

    /**
     * @return list<string>
     */
    protected function barColors(int $count): array
    {
        return array_map(
            fn (int $index): string => self::BAR_PALETTE[$index % count(self::BAR_PALETTE)],
            range(0, max($count - 1, 0)),
        );
    }
}
