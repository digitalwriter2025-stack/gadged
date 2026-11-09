<?php

namespace App\Enums;

use Illuminate\Support\Str;

/**
 * Every staff permission, named "<module>.<ability>". The source of truth for the
 * permissions table (see RolesAndPermissionsSeeder) and for policy checks.
 */
enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case ReportsView = 'reports.view';
    case ActivityLogView = 'activity_log.view';

    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsUpdate = 'products.update';
    case ProductsDelete = 'products.delete';
    case ProductsViewCostPrice = 'products.view_cost_price';

    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';

    case BrandsView = 'brands.view';
    case BrandsCreate = 'brands.create';
    case BrandsUpdate = 'brands.update';
    case BrandsDelete = 'brands.delete';

    case AttributesManage = 'attributes.manage';
    case SpecificationsManage = 'specifications.manage';

    case InventoryView = 'inventory.view';
    case InventoryManage = 'inventory.manage';

    case OrdersView = 'orders.view';
    case OrdersCreate = 'orders.create';
    case OrdersUpdate = 'orders.update';
    case OrdersCancel = 'orders.cancel';
    case OrdersRefund = 'orders.refund';

    case PaymentsView = 'payments.view';
    case PaymentsManage = 'payments.manage';

    case FinanceView = 'finance.view';
    case FinanceManage = 'finance.manage';

    case CouponsView = 'coupons.view';
    case CouponsManage = 'coupons.manage';

    case FlashSalesView = 'flash_sales.view';
    case FlashSalesManage = 'flash_sales.manage';

    case ReviewsView = 'reviews.view';
    case ReviewsModerate = 'reviews.moderate';

    case CustomersView = 'customers.view';
    case CustomersUpdate = 'customers.update';
    case CustomersDelete = 'customers.delete';

    case ContentManage = 'content.manage';
    case ShippingManage = 'shipping.manage';
    case SettingsManage = 'settings.manage';
    case StaffManage = 'staff.manage';
    case RolesManage = 'roles.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * The admin section this permission belongs to, e.g. "products".
     */
    public function section(): string
    {
        return Str::before($this->value, '.');
    }

    /**
     * What the permission allows inside its section, e.g. "View Cost Price".
     */
    public function abilityLabel(): string
    {
        return Str::headline(Str::after($this->value, '.'));
    }

    public static function sectionLabel(string $section): string
    {
        return match ($section) {
            'dashboard' => 'Dashboard',
            'reports' => 'Reports',
            'activity_log' => 'Activity Log',
            'attributes' => 'Product Attributes',
            'specifications' => 'Product Specifications',
            'flash_sales' => 'Flash Sales',
            'content' => 'Content (Banners, Campaigns, Pages, Menus)',
            'settings' => 'Site Settings',
            'staff' => 'Staff Accounts',
            'roles' => 'Roles',
            default => Str::headline($section),
        };
    }

    /**
     * Every permission grouped by section, in declaration order.
     *
     * @return array<string, array<string, string>> section => [permission value => ability label]
     */
    public static function sections(): array
    {
        $sections = [];

        foreach (self::cases() as $permission) {
            $sections[$permission->section()][$permission->value] = $permission->abilityLabel();
        }

        return $sections;
    }
}
