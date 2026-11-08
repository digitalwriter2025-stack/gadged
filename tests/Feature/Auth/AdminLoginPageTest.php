<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_the_branded_layout(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('gx-auth-brand', false)
            ->assertSee('Welcome back')
            ->assertSee('Sign in to manage your store.');
    }

    public function test_password_reset_request_page_renders_the_branded_layout(): void
    {
        $this->get('/admin/password-reset/request')
            ->assertOk()
            ->assertSee('gx-auth-brand', false);
    }
}
