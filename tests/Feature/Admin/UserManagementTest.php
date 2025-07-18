<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_view_users_list()
    {
        $users = User::factory()->count(3)->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200)
            ->assertViewIs('admin.users.index')
            ->assertSee($users[0]->name)
            ->assertSee($users[0]->email);
    }

    public function test_admin_can_search_users()
    {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'john']));

        $response->assertStatus(200)
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    public function test_admin_can_filter_users_by_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'admin']));

        $response->assertStatus(200)
            ->assertSee($admin->name)
            ->assertDontSee($user->name);
    }

    public function test_admin_can_edit_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200)
            ->assertViewIs('admin.users.edit')
            ->assertSee($user->name)
            ->assertSee($user->email);
    }

    public function test_admin_can_update_user()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->withToken(csrf_token())
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'admin',
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_update_user_without_changing_password()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'password' => bcrypt('original_password'),
        ]);

        $originalPassword = $user->password;

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->withToken(csrf_token())
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'user',
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertEquals($originalPassword, $user->password);
    }

    public function test_admin_can_update_user_with_new_password()
    {
        $user = User::factory()->create(['role' => 'user']);
        $originalPassword = $user->password;

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->withToken(csrf_token())
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'role' => 'user',
                'password' => 'new_password',
                'password_confirmation' => 'new_password',
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertNotEquals($originalPassword, $user->password);
    }

    public function test_admin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.index'))
            ->withToken(csrf_token())
            ->delete(route('admin.users.destroy', $this->admin));

        $response->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_delete_other_users()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.index'))
            ->withToken(csrf_token())
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_validation_rules_are_enforced()
    {
        $user = User::factory()->create(['role' => 'user']);
        $existingUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->withToken(csrf_token())
            ->put(route('admin.users.update', $user), [
                'name' => '', // Invalid: empty
                'email' => $existingUser->email, // Invalid: already exists
                'role' => 'invalid_role', // Invalid: not in allowed list
                'password' => 'short', // Invalid: too short
                'password_confirmation' => 'different', // Invalid: doesn't match
            ]);

        $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }
}
