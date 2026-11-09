<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Admin-panel roles and the permissions each one is granted by default.
 * Super Admin is additionally allowed everything through Gate::before.
 */
enum StaffRole: string implements HasLabel
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Manager = 'manager';
    case ProductManager = 'product_manager';
    case OrderManager = 'order_manager';
    case ContentManager = 'content_manager';

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        $all = Permission::cases();

        return match ($this) {
            self::SuperAdmin => $all,
            self::Admin => array_values(array_filter($all, fn (Permission $p): bool => $p !== Permission::RolesManage)),
            self::Manager => [
                Permission::DashboardView, Permission::ReportsView, Permission::ActivityLogView,
                Permission::ProductsView, Permission::ProductsCreate, Permission::ProductsUpdate,
                Permission::CategoriesView, Permission::CategoriesCreate, Permission::CategoriesUpdate,
                Permission::BrandsView, Permission::BrandsCreate, Permission::BrandsUpdate,
                Permission::AttributesManage, Permission::SpecificationsManage,
                Permission::InventoryView, Permission::InventoryManage,
                Permission::OrdersView, Permission::OrdersCreate, Permission::OrdersUpdate,
                Permission::OrdersCancel, Permission::OrdersRefund,
                Permission::PaymentsView, Permission::PaymentsManage,
                Permission::CouponsView, Permission::CouponsManage,
                Permission::FlashSalesView, Permission::FlashSalesManage,
                Permission::ReviewsView, Permission::ReviewsModerate,
                Permission::CustomersView, Permission::CustomersUpdate,
                Permission::ContentManage,
            ],
            self::ProductManager => [
                Permission::DashboardView,
                Permission::ProductsView, Permission::ProductsCreate, Permission::ProductsUpdate,
                Permission::ProductsDelete, Permission::ProductsViewCostPrice,
                Permission::CategoriesView, Permission::CategoriesCreate, Permission::CategoriesUpdate,
                Permission::CategoriesDelete,
                Permission::BrandsView, Permission::BrandsCreate, Permission::BrandsUpdate, Permission::BrandsDelete,
                Permission::AttributesManage, Permission::SpecificationsManage,
                Permission::InventoryView, Permission::InventoryManage,
                Permission::CouponsView, Permission::FlashSalesView,
                Permission::ReviewsView, Permission::ReviewsModerate,
            ],
            self::OrderManager => [
                Permission::DashboardView,
                Permission::OrdersView, Permission::OrdersCreate, Permission::OrdersUpdate,
                Permission::OrdersCancel, Permission::OrdersRefund,
                Permission::PaymentsView, Permission::PaymentsManage,
                Permission::CustomersView, Permission::CustomersUpdate,
                Permission::ProductsView, Permission::InventoryView, Permission::CouponsView,
            ],
            self::ContentManager => [
                Permission::DashboardView,
                Permission::ContentManage,
                Permission::ProductsView, Permission::CategoriesView, Permission::BrandsView,
                Permission::FlashSalesView,
                Permission::ReviewsView, Permission::ReviewsModerate,
            ],
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Human label for any role name, built-in or custom.
     */
    public static function labelFor(string $roleName): string
    {
        return self::tryFrom($roleName)?->getLabel() ?? $roleName;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Manager => 'Manager',
            self::ProductManager => 'Product Manager',
            self::OrderManager => 'Order Manager',
            self::ContentManager => 'Content Manager',
        };
    }
}
