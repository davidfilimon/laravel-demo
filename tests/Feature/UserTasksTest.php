<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserTasksTest extends TestCase
{
    use LazilyRefreshDatabase;

    #[TestWith([true, 1, true])]
    #[TestWith([true, 2, true])]
    #[TestWith([true, null, true])]
    #[TestWith([false, 1, true])]
    #[TestWith([false, 2, false])]
    #[TestWith([false, null, false])]
    public function test_task_policy_allows_only_owners_and_admins(bool $admin, ?int $owner, bool $allowed): void
    {
        $user = User::factory()->make(['id' => 1, 'is_admin' => $admin]);
        $task = Task::factory()->make(['user_id' => $owner]);

        foreach (['view', 'update', 'delete'] as $ability) {
            $response = Gate::forUser($user)->inspect($ability, $task);
            $this->assertSame($allowed, $response->allowed());
            if (! $allowed) {
                $this->assertSame(404, $response->status());
            }
        }
    }

    public function test_users_see_only_their_own_tasks_statistics_and_completion_button(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['title' => 'My pending task']);
        Task::factory()->for($user)->completed()->create(['title' => 'My completed task']);
        Task::factory()->completed()->create(['title' => 'Someone else’s task']);
        Task::factory()->create(['title' => 'Legacy task', 'user_id' => null]);

        $this->actingAs($user)->get(route('dashboard'))->assertOk()
            ->assertSee('My pending task')->assertSee('My completed task')
            ->assertDontSee('Someone else’s task')->assertDontSee('Legacy task')
            ->assertDontSee('Manage users')->assertDontSee('name="user_id"', false)
            ->assertViewHas('stats', ['total' => 2, 'completed' => 1, 'pending' => 1, 'percentage' => 50]);
    }

    public function test_other_users_completed_tasks_do_not_show_clear_button(): void
    {
        $user = User::factory()->create();
        Task::factory()->completed()->create();

        $this->actingAs($user)->get(route('tasks.index'))->assertOk()->assertDontSee('Clear Completed Tasks');
    }

    #[TestWith(['GET', 'tasks.show'])]
    #[TestWith(['GET', 'tasks.edit'])]
    #[TestWith(['PUT', 'tasks.update'])]
    #[TestWith(['PATCH', 'tasks.completion'])]
    #[TestWith(['DELETE', 'tasks.destroy'])]
    public function test_users_cannot_access_or_modify_someone_elses_task(string $method, string $route): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['title' => 'Private task']);

        $this->actingAs($user)->call($method, route($route, $task), ['title' => 'Hacked', 'priority' => 'low', 'is_completed' => true])->assertNotFound();

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Private task', 'user_id' => $task->user_id, 'is_completed' => false]);
    }

    public function test_users_can_create_and_manage_their_own_tasks(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('tasks.store'), ['title' => 'My task', 'priority' => 'high'])->assertRedirect(route('tasks.index'));
        $task = $user->tasks()->sole();
        $this->get(route('tasks.show', $task))->assertOk()->assertSee('My task');
        $this->get(route('tasks.edit', $task))->assertOk()->assertDontSee('name="user_id"', false);
        $this->put(route('tasks.update', $task), ['title' => 'Updated', 'priority' => 'low'])->assertRedirect(route('tasks.index'));
        $this->patch(route('tasks.completion', $task), ['is_completed' => true])->assertRedirect();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated', 'user_id' => $user->id, 'is_completed' => true]);
        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertModelMissing($task);
    }

    #[TestWith(['POST'])]
    #[TestWith(['PUT'])]
    public function test_users_cannot_assign_tasks_by_forging_user_id(string $method): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::factory()->for($user)->create(['title' => 'Original']);
        $url = $method === 'POST' ? route('tasks.store') : route('tasks.update', $task);

        $this->actingAs($user)->call($method, $url, ['title' => 'Changed', 'priority' => 'low', 'user_id' => $other->id])->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('tasks', 1);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Original', 'user_id' => $user->id]);
    }

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith([[]])]
    public function test_empty_assignment_cannot_unassign_a_users_task(mixed $value): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)->put(route('tasks.update', $task), ['title' => 'Updated', 'priority' => 'high', 'user_id' => $value])->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'user_id' => $user->id, 'title' => 'Updated']);
    }

    public function test_users_cannot_clear_other_users_completed_tasks(): void
    {
        $user = User::factory()->create();
        $ownCompleted = Task::factory()->for($user)->completed()->create();
        $pending = Task::factory()->for($user)->create();
        $otherCompleted = Task::factory()->completed()->create();

        $this->actingAs($user)->delete(route('tasks.clear-completed'))->assertRedirect(route('tasks.index'));

        $this->assertModelMissing($ownCompleted);
        $this->assertModelExists($pending);
        $this->assertModelExists($otherCompleted);
    }

    public function test_admin_can_assign_and_reassign_tasks_and_users_gain_and_lose_access(): void
    {
        $admin = User::factory()->admin()->create();
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->actingAs($admin)->post(route('tasks.store'), ['title' => 'Assigned work', 'priority' => 'high', 'user_id' => $first->id])->assertRedirect(route('tasks.index'));
        $task = Task::sole();
        $this->assertSame($first->id, $task->user_id);
        $this->actingAs($first)->get(route('tasks.show', $task))->assertOk();
        $this->actingAs($second)->get(route('tasks.show', $task))->assertNotFound();
        $this->actingAs($admin)->put(route('tasks.update', $task), ['title' => 'Assigned work', 'priority' => 'medium', 'user_id' => $second->id])->assertRedirect(route('tasks.index'));
        $this->actingAs($first)->get(route('tasks.show', $task))->assertNotFound();
        $this->actingAs($second)->get(route('tasks.show', $task))->assertOk();
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'user_id' => $second->id]);
    }

    public function test_admin_can_find_users_filter_tasks_and_assign_legacy_tasks(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Alice']);
        $legacy = Task::factory()->create(['title' => 'Legacy work', 'user_id' => null]);
        Task::factory()->for($user)->create(['title' => 'Alice work']);

        $this->actingAs($admin)->get(route('tasks.index'))->assertOk()->assertSee('Alice')->assertSee('Legacy work')->assertSee('Unassigned');
        $this->get(route('tasks.index', ['user_id' => $user->id]))->assertOk()->assertSee('Alice work')->assertDontSee('Legacy work');
        $this->get(route('tasks.edit', $legacy))->assertOk()->assertSee('Assigned to');
        $this->put(route('tasks.update', $legacy), ['title' => 'Legacy work', 'priority' => 'high', 'user_id' => $user->id])->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['id' => $legacy->id, 'user_id' => $user->id]);
    }

    public function test_assignments_must_reference_an_existing_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('tasks.store'), ['title' => 'Invalid assignment', 'priority' => 'high', 'user_id' => 999])->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_regular_users_cannot_filter_by_other_users(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)->get(route('tasks.index', ['user_id' => $other->id]))->assertSessionHasErrors('user_id');
    }
}
