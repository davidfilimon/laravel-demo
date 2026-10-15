<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_creates_admin_with_hashed_password(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Name', 'Admin')
            ->expectsQuestion('Password (at least 12 characters)', 'a-long-test-password')
            ->expectsQuestion('Confirm password', 'a-long-test-password')
            ->assertSuccessful();

        $admin = User::where('email', 'admin@example.com')->sole();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('a-long-test-password', $admin->password));
    }

    public function test_command_does_not_overwrite_or_promote_an_existing_account(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $this->artisan('app:create-admin')
            ->expectsQuestion('Email', 'existing@example.com')
            ->expectsQuestion('Name', 'Changed')
            ->expectsQuestion('Password (at least 12 characters)', 'a-long-test-password')
            ->expectsQuestion('Confirm password', 'a-long-test-password')
            ->assertFailed();

        $this->assertDatabaseCount('users', 1);
        $this->assertFalse($user->fresh()->is_admin);
        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_command_rejects_weak_passwords(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Name', 'Admin')
            ->expectsQuestion('Password (at least 12 characters)', 'short')
            ->expectsQuestion('Confirm password', 'short')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }
}
