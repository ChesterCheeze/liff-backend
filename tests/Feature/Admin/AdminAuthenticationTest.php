<?php

namespace Tests\Feature\Admin;

use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some test data for analytics
        Survey::factory(5)->create();
        SurveyResponse::factory(20)->create();
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_admin_dashboard()
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_middleware_properly_filters_requests()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Test all admin routes with non-admin user
        $adminRoutes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.surveys.index'),
            route('admin.analytics'),
        ];

        foreach ($adminRoutes as $route) {
            $this->actingAs($user, 'admin')
                ->get($route)
                ->assertStatus(403);
        }

        // Test all admin routes with admin user
        foreach ($adminRoutes as $route) {
            $response = $this->actingAs($admin, 'admin')->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_user_role_changes_affect_admin_access()
    {
        $user = User::factory()->create(['role' => 'user']);

        // Initially cannot access admin area
        $this->actingAs($user, 'admin')
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        // Update to admin role
        $user->update(['role' => 'admin']);

        // Should now have access
        $this->actingAs($user, 'admin')
            ->get(route('admin.dashboard'))
            ->assertStatus(200);
    }
}
