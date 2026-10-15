<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_regular_user_with_hashed_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Alice', 'email' => 'ALICE@example.com', 'password' => 'a-long-test-password',
            'password_confirmation' => 'a-long-test-password', 'is_admin' => true,
        ])->assertRedirect(route('users.index'))->assertSessionHas('success');

        $user = User::where('email', 'alice@example.com')->sole();
        $this->assertSame('Alice', $user->name);
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('a-long-test-password', $user->password));
        $this->get(route('users.index'))->assertOk()->assertSee('Alice')->assertSee('alice@example.com');
    }

    public function test_user_names_are_escaped_in_the_user_list_and_assignment_form(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => '<script>alert(1)</script>']);

        $this->actingAs($admin)->get(route('users.index'))->assertOk()->assertSee($user->name)->assertDontSee($user->name, false);
        $this->get(route('tasks.create'))->assertOk()->assertSee($user->name)->assertDontSee($user->name, false);
    }

    public function test_regular_users_cannot_view_or_create_users(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.index'))->assertForbidden();
        $this->post(route('users.store'), ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'a-long-password', 'password_confirmation' => 'a-long-password'])->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_new_user_requires_name_email_and_password(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [])->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    #[TestWith(['email', 'not-an-email'])]
    #[TestWith(['email', 'taken@example.com'])]
    #[TestWith(['password', 'short'])]
    #[TestWith(['password_confirmation', 'different-password'])]
    #[TestWith(['name', ['invalid']])]
    public function test_invalid_user_input_does_not_create_an_account(string $field, mixed $value): void
    {
        $admin = User::factory()->admin()->create(['email' => 'taken@example.com']);
        $payload = ['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'a-long-test-password', 'password_confirmation' => 'a-long-test-password'];
        $payload[$field] = $value;

        $this->actingAs($admin)->post(route('users.store'), $payload)->assertSessionHasErrors($field === 'password_confirmation' ? 'password' : $field);

        $this->assertDatabaseCount('users', 1);
    }
}
