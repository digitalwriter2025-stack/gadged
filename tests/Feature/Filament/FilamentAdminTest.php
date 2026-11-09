<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\ProductStatus;
use App\Enums\StaffRole;
use App\Enums\UserStatus;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate(StaffRole::SuperAdmin->value);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'status' => UserStatus::Active,
        ]);

        $this->admin->assignRole(StaffRole::SuperAdmin->value);
    }

    public function test_admin_can_access_filament_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_cache_manager(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/manage-cache');

        $response->assertSuccessful()
            ->assertSee('Flush All Caches')
            ->assertSee('wire:click="clearAllCache"', false)
            ->assertSee('wire:click="flushCatalogCache"', false)
            ->assertSee('wire:click="flushSettingsCache"', false)
            ->assertSee('wire:click="clearViewCache"', false)
            ->assertSee('wire:click="clearRouteConfigCache"', false);
    }

    public function test_admin_can_access_site_settings(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/manage-site-settings');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_home_sections_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/home-sections');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_banners_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/banners');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_products_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/products');

        $response->assertSuccessful();
    }

    public function test_products_list_renders_status_badges(): void
    {
        Product::factory()->create(['name' => 'Listed Phone', 'status' => ProductStatus::Active]);

        $this->actingAs($this->admin)
            ->get('/admin/products')
            ->assertSuccessful()
            ->assertSee('Listed Phone')
            ->assertSee('Active');
    }

    public function test_admin_can_access_categories_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/categories');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_brands_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/brands');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_offers_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/offers');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_flash_sales_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/flash-sales');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_pages_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/pages');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_menus_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/menus');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_service_highlights_resource(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/service-highlights');

        $response->assertSuccessful();
    }
}
