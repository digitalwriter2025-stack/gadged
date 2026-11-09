<?php

namespace Tests\Feature\Store;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_render_in_the_storefront_layout(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'))->assertOk()->assertSee('Login')->assertSee(route('register'));
        $this->get(route('register'))->assertOk()->assertSee('Create Account');
        $this->get(route('password.request'))->assertOk()->assertSee('Forgot Password');
        $this->get(route('password.reset', ['token' => Password::createToken($user), 'email' => $user->email]))
            ->assertOk()
            ->assertSee('Reset Password')
            ->assertSee($user->email);
    }

    public function test_customer_registers_with_phone_and_optional_email(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Rahim Uddin',
            'phone' => '+8801712-345678',
            'email' => '',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect('/account');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['name' => 'Rahim Uddin', 'phone' => '01712345678', 'email' => null, 'status' => 'active']);
    }

    public function test_registration_rejects_invalid_and_taken_phone_numbers(): void
    {
        User::factory()->create(['phone' => '01712345678']);

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Karim',
            'phone' => '01712345678',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('phone');

        $this->post(route('register.store'), [
            'name' => 'Karim',
            'phone' => '12345',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_customer_logs_in_with_phone_number(): void
    {
        $user = User::factory()->create(['phone' => '01812345678']);

        $this->post(route('login.store'), ['login' => '+880 1812-345678', 'password' => 'password'])
            ->assertRedirect('/account');

        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_logs_in_with_email(): void
    {
        $user = User::factory()->create(['email' => 'rahim@example.com']);

        $this->post(route('login.store'), ['login' => 'Rahim@Example.com', 'password' => 'password'])
            ->assertRedirect('/account');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        User::factory()->create(['phone' => '01812345678']);

        $this->post(route('login.store'), ['login' => '01812345678', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_blocked_customer_cannot_log_in(): void
    {
        User::factory()->blocked()->create(['phone' => '01812345678']);

        $this->post(route('login.store'), ['login' => '01812345678', 'password' => 'password'])
            ->assertSessionHasErrors(['login' => 'This account has been blocked. Please contact support.']);

        $this->assertGuest();
    }
}
