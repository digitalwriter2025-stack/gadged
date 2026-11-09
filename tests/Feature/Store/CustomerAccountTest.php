<?php

namespace Tests\Feature\Store;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('store.account'))->assertRedirect(route('login'));
    }

    public function test_account_page_lists_only_the_customers_orders(): void
    {
        $user = User::factory()->create(['name' => 'Rahim Uddin']);
        Order::factory()->create(['user_id' => $user->id, 'order_number' => 'GD-MINE-001', 'grand_total' => '1060.00']);
        Order::factory()->create(['user_id' => User::factory(), 'order_number' => 'GD-OTHER-001']);

        $this->actingAs($user)
            ->get(route('store.account'))
            ->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('GD-MINE-001')
            ->assertSee('৳1,060')
            ->assertDontSee('GD-OTHER-001');
    }

    public function test_account_page_summarises_order_counts_and_initials(): void
    {
        $user = User::factory()->create(['name' => 'Rahim Uddin']);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Shipped]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Cancelled]);

        $this->actingAs($user)
            ->get(route('store.account'))
            ->assertOk()
            ->assertViewHas('initials', 'RU')
            ->assertViewHas('stats', ['total' => 4, 'active' => 2, 'delivered' => 1]);
    }

    public function test_header_links_signed_in_customer_to_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('store.home'))
            ->assertOk()
            ->assertSee('href="'.route('store.account').'"', false);
    }

    public function test_customer_updates_profile_with_phone_and_optional_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('store.account'))
            ->put(route('user-profile-information.update'), [
                'name' => 'New Name',
                'phone' => '01912 345678',
                'email' => '',
            ])
            ->assertRedirect(route('store.account'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'phone' => '01912345678', 'email' => null]);
    }

    public function test_profile_needs_a_phone_or_an_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('user-profile-information.update'), ['name' => 'New Name', 'phone' => '', 'email' => ''])
            ->assertSessionHasErrorsIn('updateProfileInformation', ['phone', 'email']);
    }
}
