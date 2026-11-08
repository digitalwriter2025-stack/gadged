<?php

namespace Tests\Unit\Enums;

use App\Enums\InventoryTransactionType;
use App\Enums\OrderStatus;
use App\Enums\Permission;
use App\Enums\StaffRole;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_happy_path_transitions_are_allowed(): void
    {
        $path = [
            OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed,
            OrderStatus::Shipped, OrderStatus::Delivered, OrderStatus::Returned, OrderStatus::Refunded,
        ];

        for ($i = 0; $i < count($path) - 1; $i++) {
            $this->assertTrue($path[$i]->canTransitionTo($path[$i + 1]), "{$path[$i]->value} → {$path[$i + 1]->value}");
        }
    }

    public function test_invalid_transitions_are_refused(): void
    {
        $this->assertFalse(OrderStatus::Pending->canTransitionTo(OrderStatus::Delivered));
        $this->assertFalse(OrderStatus::Shipped->canTransitionTo(OrderStatus::Cancelled));
        $this->assertFalse(OrderStatus::Delivered->canTransitionTo(OrderStatus::Pending));
        $this->assertFalse(OrderStatus::Cancelled->canTransitionTo(OrderStatus::Confirmed));
        $this->assertTrue(OrderStatus::Cancelled->isFinal());
        $this->assertTrue(OrderStatus::Refunded->isFinal());
    }

    public function test_cancellation_is_possible_until_shipping(): void
    {
        foreach ([OrderStatus::Pending, OrderStatus::Confirmed, OrderStatus::Processing, OrderStatus::Packed] as $status) {
            $this->assertTrue($status->canTransitionTo(OrderStatus::Cancelled), $status->value);
        }
    }

    public function test_stock_restoring_statuses(): void
    {
        $this->assertTrue(OrderStatus::Cancelled->restoresStock());
        $this->assertTrue(OrderStatus::Returned->restoresStock());
        $this->assertFalse(OrderStatus::Delivered->restoresStock());
        $this->assertSame('cancelled_at', OrderStatus::Cancelled->timestampColumn());
    }

    public function test_inventory_types_have_expected_direction(): void
    {
        $this->assertSame(1, InventoryTransactionType::StockIn->direction());
        $this->assertSame(1, InventoryTransactionType::OrderCancellation->direction());
        $this->assertSame(-1, InventoryTransactionType::Order->direction());
        $this->assertSame(-1, InventoryTransactionType::Damage->direction());
        $this->assertSame(0, InventoryTransactionType::Adjustment->direction());
        $this->assertNotContains(InventoryTransactionType::Order, InventoryTransactionType::manual());
    }

    public function test_role_permission_map_is_consistent(): void
    {
        $this->assertSame(Permission::cases(), StaffRole::SuperAdmin->permissions());
        $this->assertNotContains(Permission::RolesManage, StaffRole::Admin->permissions());

        foreach (StaffRole::cases() as $role) {
            $permissions = $role->permissions();
            $this->assertSame(count($permissions), count(array_unique(array_map(fn (Permission $p) => $p->value, $permissions))), $role->value);
            $this->assertContains(Permission::DashboardView, $permissions, $role->value);
        }
    }
}
