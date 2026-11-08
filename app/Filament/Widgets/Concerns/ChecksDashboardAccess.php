<?php

declare(strict_types=1);

namespace App\Filament\Widgets\Concerns;

use App\Enums\Permission;

/**
 * Sales widgets need report or order access; catalog widgets need product access.
 */
trait ChecksDashboardAccess
{
    protected static function canViewSales(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->can(Permission::ReportsView->value) || $user?->can(Permission::OrdersView->value));
    }

    protected static function canViewCatalog(): bool
    {
        return (bool) auth()->user()?->can(Permission::ProductsView->value);
    }
}
