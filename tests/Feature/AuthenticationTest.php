<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_form_is_available_to_guests(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in');
    }

    public function test_valid_login_authenticates_and_regenerates_session(): void
    {
        $user = User::factory()->create(['email' => 'alice@example.com']);
        $this->withSession(['existing' => 'value']);
        $session = session()->getId();

        $this->post(route('login.store'), ['email' => 'ALICE@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'))->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($session, session()->getId());
    }

    public function test_invalid_login_returns_generic_error_without_authenticating(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);

        $this->from(route('login'))->post(route('login.store'), ['email' => 'alice@example.com', 'password' => 'wrong'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'These credentials do not match our records.'])
            ->assertSessionMissing('_old_input.password');

        $this->assertGuest();
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post(route('login.store'), [])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_login_rejects_non_string_email_without_server_error(): void
    {
        $this->post(route('login.store'), ['email' => ['bad'], 'password' => 'wrong'])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['email' => 'limited@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }

        $this->post(route('login.store'), ['email' => 'limited@example.com', 'password' => 'wrong'])->assertTooManyRequests();
    }

    public function test_logout_invalidates_authentication_and_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['private' => 'data']);

        $this->post(route('logout'))->assertRedirect(route('login'))->assertSessionMissing('private');

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    #[TestWith(['GET', '/'])]
    #[TestWith(['GET', '/tasks'])]
    #[TestWith(['GET', '/tasks/create'])]
    #[TestWith(['POST', '/tasks'])]
    #[TestWith(['GET', '/tasks/1'])]
    #[TestWith(['GET', '/tasks/1/edit'])]
    #[TestWith(['PUT', '/tasks/1'])]
    #[TestWith(['PATCH', '/tasks/1/completion'])]
    #[TestWith(['DELETE', '/tasks/1'])]
    #[TestWith(['DELETE', '/tasks/completed'])]
    #[TestWith(['GET', '/users'])]
    #[TestWith(['POST', '/users'])]
    public function test_task_and_user_endpoints_require_login(string $method, string $url): void
    {
        $this->call($method, $url)->assertRedirect(route('login'));
    }
}
