<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

/**
 * The greeting lives in the WelcomeBanner widget at the top of the page.
 */
class Dashboard extends BaseDashboard
{
    /**
     * @return array<string, int>
     */
    public function getColumns(): int|array
    {
        return ['default' => 1, 'lg' => 2];
    }
}
