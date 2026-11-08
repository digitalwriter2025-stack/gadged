<?php

namespace Tests\Feature\Auth;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Models\Category;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_creates_every_permission_and_role_and_is_idempotent(): void
    {
        app(AccessControlService::class)->sync();
        app(AccessControlService::class)->sync();

        $this->assertSame(count(Permission::cases()), \Spatie\Permission\Models\Permission::query()->count());
        $this->assertEqualsCanonicalizing(StaffRole::values(), Role::query()->pluck('name')->all());
        $this->assertFalse(Role::findByName(StaffRole::Admin->value)->hasPermissionTo(Permission::RolesManage->value));
        $this->assertTrue(Role::findByName(StaffRole::Admin->value)->hasPermissionTo(Permission::SettingsManage->value));
    }

    public function test_only_active_staff_can_access_the_admin_panel(): void
    {
        $this->get('/admin/login')->assertOk();

        $customer = User::factory()->create();
        $this->actingAs($customer)->get('/admin')->assertForbidden();

        $blockedStaff = User::factory()->blocked()->staff(StaffRole::Admin)->create();
        $this->actingAs($blockedStaff)->get('/admin')->assertForbidden();

        $staff = User::factory()->staff(StaffRole::ContentManager)->create();
        $this->actingAs($staff)->get('/admin')->assertOk();
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_super_admin_passes_every_check_including_force_delete(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $product = Product::factory()->create();

        $this->assertTrue($superAdmin->can('forceDelete', $product));
        $this->assertTrue($superAdmin->can('delete', Order::factory()->create()));
        $this->assertTrue($superAdmin->can('manageRoles', User::class));
    }

    public function test_product_manager_manages_catalog_but_not_orders_or_settings(): void
    {
        $user = User::factory()->staff(StaffRole::ProductManager)->create();
        $product = Product::factory()->create();

        $this->assertTrue($user->can('create', Product::class));
        $this->assertTrue($user->can('update', $product));
        $this->assertTrue($user->can('delete', $product));
        $this->assertTrue($user->can('viewCostPrice', Product::class));
        $this->assertTrue($user->can('create', InventoryTransaction::class));
        $this->assertFalse($user->can('forceDelete', $product));
        $this->assertFalse($user->can('viewAny', Order::class));
        $this->assertFalse($user->can('update', Setting::query()->create(['group' => 'g', 'key' => 'k'])));
    }

    public function test_order_manager_handles_orders_but_cannot_change_products(): void
    {
        $user = User::factory()->staff(StaffRole::OrderManager)->create();
        $order = Order::factory()->create();
        $product = Product::factory()->create();

        $this->assertTrue($user->can('view', $order));
        $this->assertTrue($user->can('update', $order));
        $this->assertTrue($user->can('cancel', $order));
        $this->assertTrue($user->can('managePayment', $order));
        $this->assertTrue($user->can('view', $product));
        $this->assertFalse($user->can('update', $product));
        $this->assertFalse($user->can('viewCostPrice', Product::class));
        $this->assertFalse($user->can('delete', $order), 'Orders are never deleted.');
    }

    public function test_content_manager_moderates_reviews_but_cannot_touch_categories(): void
    {
        $user = User::factory()->staff(StaffRole::ContentManager)->create();

        $this->assertTrue($user->can('update', Review::factory()->create()));
        $this->assertTrue($user->can('viewAny', Category::class));
        $this->assertFalse($user->can('update', Category::factory()->create()));
    }

    public function test_customers_and_blocked_staff_get_no_permissions(): void
    {
        $customer = User::factory()->create();
        $blocked = User::factory()->blocked()->staff(StaffRole::Admin)->create();

        foreach ([$customer, $blocked] as $user) {
            $this->assertFalse($user->can('viewAny', Product::class));
            $this->assertFalse($user->can('viewAny', Order::class));
        }
    }

    public function test_ledger_rows_cannot_be_edited_or_deleted_by_staff(): void
    {
        $admin = User::factory()->staff(StaffRole::Admin)->create();
        $transaction = new InventoryTransaction;

        $this->assertTrue($admin->can('viewAny', InventoryTransaction::class));
        $this->assertFalse($admin->can('update', $transaction));
        $this->assertFalse($admin->can('delete', $transaction));
    }

    public function test_staff_accounts_need_staff_permission_and_super_admins_are_protected(): void
    {
        $manager = User::factory()->staff(StaffRole::Manager)->create();
        $admin = User::factory()->staff(StaffRole::Admin)->create();
        $superAdmin = User::factory()->superAdmin()->create();
        $customer = User::factory()->create();

        $this->assertTrue($manager->can('update', $customer));
        $this->assertFalse($manager->can('update', $admin));
        $this->assertTrue($admin->can('update', $manager));
        $this->assertFalse($admin->can('update', $superAdmin));
        $this->assertFalse($admin->can('delete', $admin), 'Users cannot delete themselves.');
        $this->assertFalse($admin->can('manageRoles', User::class));
    }
}
