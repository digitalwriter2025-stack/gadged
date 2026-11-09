<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\StaffRole;
use App\Filament\Resources\ShippingMethods\Pages\CreateShippingMethod;
use App\Filament\Resources\ShippingMethods\Pages\ListShippingMethods;
use App\Models\ShippingMethod;
use App\Models\User;
use Database\Seeders\ShippingSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShippingMethodResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShippingSeeder::class);
    }

    public function test_admin_sees_shipping_methods_with_their_charges(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(ListShippingMethods::class)
            ->assertOk()
            ->assertCanSeeTableRecords(ShippingMethod::query()->get())
            ->assertSee('Inside Dhaka')
            ->assertSee('Outside Dhaka')
            ->assertSee('1-2 working days');
    }

    public function test_admin_can_add_a_shipping_method(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        Livewire::test(CreateShippingMethod::class)
            ->fillForm([
                'name' => 'Express (Dhaka)',
                'description' => 'Same or next working day',
                'charge' => '150',
                'delivery_estimate' => 'Same / next day',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $method = ShippingMethod::query()->where('name', 'Express (Dhaka)')->sole();

        $this->assertSame('express_dhaka', $method->code);
        $this->assertSame('150.00', $method->charge);
        $this->assertSame(ShippingMethod::query()->max('sort_order'), $method->sort_order);
    }

    public function test_admin_can_duplicate_a_shipping_method(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $inside = ShippingMethod::query()->where('code', 'inside_dhaka')->sole();

        Livewire::test(ListShippingMethods::class)
            ->callAction(TestAction::make('replicate')->table($inside));

        $copy = ShippingMethod::query()->where('name', 'Inside Dhaka (copy)')->sole();

        $this->assertSame('inside_dhaka_copy', $copy->code);
        $this->assertSame($inside->charge, $copy->charge);
    }

    public function test_staff_without_shipping_permission_cannot_open_the_page(): void
    {
        $this->actingAs(User::factory()->staff(StaffRole::ContentManager)->create())
            ->get(route('filament.admin.resources.shipping-methods.index'))
            ->assertForbidden();
    }
}
