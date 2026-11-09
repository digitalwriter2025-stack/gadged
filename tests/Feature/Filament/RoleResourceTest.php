<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\Permission;
use App\Enums\StaffRole;
use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Models\User;
use App\Services\AccessControlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(AccessControlService::class)->sync();
    }

    public function test_custom_role_grants_its_sections_to_staff(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Sales Executive',
                'access' => ['orders' => [Permission::OrdersView->value, Permission::OrdersCreate->value]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::findByName('Sales Executive');
        $this->assertEqualsCanonicalizing(
            [Permission::OrdersView->value, Permission::OrdersCreate->value],
            $role->permissions->pluck('name')->all(),
        );

        $staff = User::factory()->create();
        $staff->assignRole($role);

        $this->assertTrue($staff->isStaff());
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
        $this->actingAs($staff)->get('/admin/products')->assertForbidden();
    }

    public function test_built_in_roles_cannot_be_edited_or_deleted_even_by_super_admin(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();
        $builtIn = Role::findByName(StaffRole::Admin->value);
        $custom = Role::create(['name' => 'Packer', 'guard_name' => 'web']);

        $this->assertTrue($superAdmin->can('view', $builtIn));
        $this->assertFalse($superAdmin->can('update', $builtIn));
        $this->assertFalse($superAdmin->can('delete', $builtIn));
        $this->assertTrue($superAdmin->can('update', $custom));
        $this->assertTrue($superAdmin->can('delete', $custom));

        $this->actingAs($superAdmin)->get(EditRole::getUrl(['record' => $builtIn]))->assertForbidden();
    }

    public function test_roles_section_requires_roles_permission(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::Admin)->create())
            ->get('/admin/roles')
            ->assertForbidden();
    }
}
