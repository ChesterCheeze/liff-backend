<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertViewIs('admin.login');
    }

    public function test_admin_can_login_with_valid_credentials()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_non_admin_cannot_login()
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'user@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_admin_login_requires_email()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'password' => 'password123',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_admin_login_requires_password()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'admin@example.com',
            ]);

        $response->assertSessionHasErrors(['password']);
        $this->assertGuest('admin');
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_admin_login_fails_with_mismatched_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertRedirect('/admin/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/logout');

        $response->assertRedirect('/admin/login');
        $response->assertSessionHas('success', 'You have been logged out.');
        $this->assertGuest('admin');
    }

    public function test_unauthenticated_user_redirected_to_admin_login()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_non_admin_gets_403()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $this->actingAs($user, 'admin');

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_login_redirects_to_dashboard_by_default()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'password123',
            ]);

        $response->assertRedirect('/admin/dashboard');
    }
}
