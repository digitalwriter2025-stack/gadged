<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Filament\Auth\Login;
use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\StaffAccessService;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(AccessControlService::class)->sync();
    }

    public function test_super_admin_creates_staff_with_only_the_selected_sections(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateStaff::class)
            ->fillForm([
                'name' => 'Rahim Uddin',
                'username' => 'Rahim',
                'email' => 'rahim@example.com',
                'password' => 'secret-pass-123',
                'access' => [
                    'products' => [Permission::ProductsView->value, Permission::ProductsUpdate->value],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $staff = User::query()->where('username', 'rahim')->sole();

        $this->assertTrue(Hash::check('secret-pass-123', $staff->password));
        $this->assertTrue($staff->isStaff());
        $this->assertEqualsCanonicalizing(
            [Permission::ProductsView->value, Permission::ProductsUpdate->value],
            $staff->getAllPermissions()->pluck('name')->all(),
        );

        $this->actingAs($staff);
        $this->get('/admin/products')->assertOk();
        $this->get('/admin/orders')->assertForbidden();
        $this->get('/admin/manage-site-settings')->assertForbidden();
        $this->get('/admin/staff')->assertForbidden();
    }

    public function test_staff_needs_a_role_or_at_least_one_section(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateStaff::class)
            ->fillForm([
                'name' => 'No Access',
                'username' => 'noaccess',
                'email' => 'none@example.com',
                'password' => 'secret-pass-123',
            ])
            ->call('create');

        $this->assertFalse(User::query()->where('username', 'noaccess')->exists());
    }

    public function test_staff_can_sign_in_with_username_or_email(): void
    {
        $staff = User::factory()->staff(StaffRole::OrderManager)->create([
            'username' => 'karim',
            'email' => 'karim@example.com',
            'password' => 'secret-pass-123',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'karim', 'password' => 'secret-pass-123'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($staff);

        auth()->logout();

        Livewire::test(Login::class)
            ->fillForm(['email' => 'karim@example.com', 'password' => 'secret-pass-123'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($staff);
    }

    public function test_super_admin_account_cannot_be_edited_or_deleted_only_its_password_changed(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $otherSuperAdmin = User::factory()->superAdmin()->create();
        $admin = User::factory()->staff(StaffRole::Admin)->create();

        foreach ([$superAdmin, $otherSuperAdmin, $admin] as $actor) {
            $this->assertFalse($actor->can('update', $superAdmin));
            $this->assertFalse($actor->can('delete', $superAdmin));
            $this->assertFalse($actor->can('forceDelete', $superAdmin));
        }

        $this->assertTrue($superAdmin->can('changePassword', $superAdmin));
        $this->assertFalse($admin->can('changePassword', $superAdmin));

        $this->actingAs($superAdmin)->get(EditStaff::getUrl(['record' => $superAdmin]))->assertForbidden();

        Livewire::test(ListStaff::class)
            ->assertActionHidden(TestAction::make('edit')->table($superAdmin))
            ->assertActionHidden(TestAction::make('delete')->table($superAdmin))
            ->callAction(TestAction::make('changePassword')->table($superAdmin), [
                'password' => 'brand-new-pass-1',
                'password_confirmation' => 'brand-new-pass-1',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified('Password changed');

        $this->assertTrue(Hash::check('brand-new-pass-1', $superAdmin->refresh()->password));
        $this->assertTrue($superAdmin->isSuperAdmin());
    }

    public function test_super_admin_role_is_never_assignable(): void
    {
        $roles = app(StaffAccessService::class)->assignableRoles(User::factory()->superAdmin()->create());

        $this->assertArrayNotHasKey(StaffRole::SuperAdmin->value, $roles);
        $this->assertArrayHasKey(StaffRole::Admin->value, $roles);
    }

    public function test_managers_can_only_grant_access_they_hold(): void
    {
        $admin = User::factory()->staff(StaffRole::Admin)->create();
        $staff = User::factory()->staff(StaffRole::ContentManager)->create();
        $service = app(StaffAccessService::class);

        $this->assertArrayNotHasKey('roles', $service->grantableSections($admin));

        $service->sync($admin, $staff, StaffRole::OrderManager->value, [Permission::RolesManage->value, Permission::FinanceView->value]);

        $staff->refresh();
        $this->assertTrue($staff->hasRole(StaffRole::OrderManager->value));
        $this->assertFalse($staff->hasRole(StaffRole::ContentManager->value));
        $this->assertTrue($staff->hasDirectPermission(Permission::FinanceView->value));
        $this->assertFalse($staff->hasDirectPermission(Permission::RolesManage->value));
    }

    public function test_staff_cannot_change_their_own_access(): void
    {
        $admin = User::factory()->staff(StaffRole::Admin)->create();

        app(StaffAccessService::class)->sync($admin, $admin, StaffRole::Admin->value, [Permission::RolesManage->value]);

        $this->assertFalse($admin->refresh()->hasDirectPermission(Permission::RolesManage->value));
    }

    public function test_editing_staff_replaces_their_sections(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $staff = User::factory()->create(['username' => 'salma']);
        $staff->givePermissionTo(Permission::OrdersView->value);

        Livewire::test(EditStaff::class, ['record' => $staff->getRouteKey()])
            ->assertFormSet(['access.orders' => [Permission::OrdersView->value]])
            ->fillForm([
                'role' => StaffRole::ContentManager->value,
                'access' => ['orders' => [], 'coupons' => [Permission::CouponsView->value]],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $staff->refresh();
        $this->assertTrue($staff->hasRole(StaffRole::ContentManager->value));
        $this->assertEquals([Permission::CouponsView->value], $staff->getDirectPermissions()->pluck('name')->all());
    }

    public function test_settings_pages_require_settings_permission(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ContentManager)->create());

        $this->get('/admin/manage-cache')->assertForbidden();
        $this->get('/admin/manage-steadfast-courier')->assertForbidden();
        $this->get('/admin/offers')->assertOk();

        $this->actingAs(User::factory()->staff(StaffRole::OrderManager)->create());

        $this->get('/admin/offers')->assertForbidden();
    }
}
