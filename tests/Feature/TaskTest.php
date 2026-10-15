<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_empty_dashboard_displays_zero_statistics_and_create_prompt(): void
    {
        $this->get(route('dashboard'))->assertOk()
            ->assertViewHas('stats', ['total' => 0, 'completed' => 0, 'pending' => 0, 'percentage' => 0])
            ->assertSee('Create your first task');
    }

    public function test_dashboard_displays_tasks_and_overall_statistics(): void
    {
        Task::factory()->completed()->create(['title' => 'Finished task']);
        Task::factory()->count(2)->create();

        $this->get(route('tasks.index'))->assertOk()->assertSee('Finished task')
            ->assertViewHas('stats', ['total' => 3, 'completed' => 1, 'pending' => 2, 'percentage' => 33]);
    }

    public function test_dashboard_displays_full_completion(): void
    {
        Task::factory()->completed()->count(2)->create();

        $this->get(route('dashboard'))->assertOk()
            ->assertViewHas('stats', ['total' => 2, 'completed' => 2, 'pending' => 0, 'percentage' => 100]);
    }

    public function test_create_form_is_accessible(): void
    {
        $this->get(route('tasks.create'))->assertOk()->assertSee('Create task');
    }

    #[TestWith(['low'])]
    #[TestWith(['medium'])]
    #[TestWith(['high'])]
    public function test_valid_task_is_created_with_each_priority(string $priority): void
    {
        $this->post(route('tasks.store'), [
            'title' => 'Prepare release', 'description' => 'Review changes', 'priority' => $priority,
            'is_completed' => '1', 'id' => 999,
        ])->assertRedirect(route('tasks.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Prepare release', 'description' => 'Review changes', 'priority' => $priority,
            'is_completed' => true,
        ]);
        $this->assertDatabaseMissing('tasks', ['id' => 999]);
        $this->assertDatabaseCount('tasks', 1);
    }

    public function test_optional_fields_default_to_pending_and_null_description(): void
    {
        $this->post(route('tasks.store'), ['title' => 'Simple task', 'priority' => 'medium'])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['title' => 'Simple task', 'description' => null, 'is_completed' => false]);
    }

    public function test_task_details_and_edit_form_display_saved_values(): void
    {
        $task = Task::factory()->create(['title' => 'My task', 'description' => "First step\nSecond step"]);

        $this->get(route('tasks.show', $task))->assertOk()->assertSee('My task')->assertSee("First step\nSecond step");
        $this->get(route('tasks.edit', $task))->assertOk()->assertSee('My task')->assertSee('Save changes');
    }

    public function test_task_can_be_updated(): void
    {
        $task = Task::factory()->create();

        $this->put(route('tasks.update', $task), [
            'title' => 'Updated title', 'description' => 'Updated description', 'priority' => 'high', 'is_completed' => true,
        ])->assertRedirect(route('tasks.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id, 'title' => 'Updated title', 'description' => 'Updated description',
            'priority' => 'high', 'is_completed' => true,
        ]);
    }

    public function test_update_can_clear_description_and_completion(): void
    {
        $task = Task::factory()->completed()->create();

        $this->put(route('tasks.update', $task), ['title' => 'Reopened', 'description' => '', 'priority' => 'low'])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'description' => null, 'is_completed' => false]);
    }

    #[TestWith([false, true])]
    #[TestWith([true, false])]
    public function test_completion_control_changes_only_status_and_returns_to_filters(bool $before, bool $after): void
    {
        $task = Task::factory()->create(['title' => 'Keep my title', 'priority' => 'high', 'is_completed' => $before]);
        $url = route('tasks.index', ['status' => 'pending', 'priority' => 'high']);

        $this->from($url)->patch(route('tasks.completion', $task), ['is_completed' => $after, 'title' => 'Ignore me'])
            ->assertRedirect($url)->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Keep my title', 'priority' => 'high', 'is_completed' => $after]);
    }

    #[TestWith([[]])]
    #[TestWith([['is_completed' => 'invalid']])]
    public function test_completion_rejects_missing_or_invalid_boolean(array $payload): void
    {
        $task = Task::factory()->create();

        $this->patch(route('tasks.completion', $task), $payload)->assertSessionHasErrors('is_completed');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'is_completed' => false]);
    }

    public function test_task_can_be_deleted_without_affecting_other_tasks(): void
    {
        $task = Task::factory()->create();
        $other = Task::factory()->create();

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'))->assertSessionHas('success');

        $this->assertModelMissing($task);
        $this->assertModelExists($other);
    }

    public function test_required_fields_are_validated_and_no_task_is_created(): void
    {
        $this->post(route('tasks.store'), [])->assertSessionHasErrors([
            'title' => 'The title field is required.', 'priority' => 'The priority field is required.',
        ]);

        $this->assertDatabaseCount('tasks', 0);
    }

    /** @return array<string, array{string, mixed, string}> */
    public static function invalidTaskFields(): array
    {
        return [
            'blank title' => ['title', '   ', 'The title field is required.'],
            'long title' => ['title', str_repeat('a', 256), 'The title field must not be greater than 255 characters.'],
            'non-string title' => ['title', ['invalid'], 'The title field must be a string.'],
            'long description' => ['description', str_repeat('a', 10001), 'The description field must not be greater than 10000 characters.'],
            'non-string description' => ['description', ['invalid'], 'The description field must be a string.'],
            'invalid priority' => ['priority', 'urgent', 'The selected priority is invalid.'],
            'array priority' => ['priority', ['high'], 'The selected priority is invalid.'],
            'invalid completion' => ['is_completed', 'yes', 'The is completed field must be true or false.'],
        ];
    }

    #[DataProvider('invalidTaskFields')]
    public function test_invalid_fields_prevent_creation_and_preserve_form_input(string $field, mixed $value, string $message): void
    {
        $payload = ['title' => 'Valid title', 'description' => 'Valid description', 'priority' => 'medium', 'is_completed' => false];
        $payload[$field] = $value;

        $this->from(route('tasks.create'))->post(route('tasks.store'), $payload)
            ->assertRedirect(route('tasks.create'))->assertSessionHasErrors([$field => $message])->assertSessionHasInput('priority');

        $this->assertDatabaseCount('tasks', 0);
    }

    #[DataProvider('invalidTaskFields')]
    public function test_invalid_fields_prevent_updates(string $field, mixed $value, string $message): void
    {
        $task = Task::factory()->create(['title' => 'Original title', 'description' => 'Original description', 'priority' => 'low']);
        $payload = ['title' => 'Changed title', 'description' => 'Changed description', 'priority' => 'high', 'is_completed' => true];
        $payload[$field] = $value;

        $this->put(route('tasks.update', $task), $payload)->assertSessionHasErrors([$field => $message]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Original title', 'description' => 'Original description', 'priority' => 'low', 'is_completed' => false]);
    }

    public function test_maximum_length_fields_are_accepted(): void
    {
        $this->post(route('tasks.store'), ['title' => str_repeat('a', 255), 'description' => str_repeat('b', 10000), 'priority' => 'low'])
            ->assertRedirect(route('tasks.index'))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('tasks', 1);
    }

    #[TestWith(['pending', 'high', ['Pending high']])]
    #[TestWith(['completed', 'high', ['Completed high']])]
    #[TestWith(['pending', '', ['Pending high', 'Pending low']])]
    #[TestWith(['', 'high', ['Completed high', 'Pending high']])]
    #[TestWith(['', 'medium', []])]
    public function test_filters_return_matching_tasks_without_changing_statistics(string $status, string $priority, array $titles): void
    {
        Task::factory()->create(['title' => 'Pending high', 'priority' => 'high']);
        Task::factory()->completed()->create(['title' => 'Completed high', 'priority' => 'high']);
        Task::factory()->create(['title' => 'Pending low', 'priority' => 'low']);

        $response = $this->get(route('tasks.index', compact('status', 'priority')))->assertOk()
            ->assertViewHas('stats', ['total' => 3, 'completed' => 1, 'pending' => 2, 'percentage' => 33]);

        $this->assertSame($titles, $response['tasks']->pluck('title')->sort()->values()->all());
    }

    #[TestWith(['status', 'invalid'])]
    #[TestWith(['priority', 'urgent'])]
    #[TestWith(['status', ['pending']])]
    #[TestWith(['priority', ['high']])]
    public function test_invalid_filter_values_are_rejected(string $field, mixed $value): void
    {
        $this->get(route('tasks.index', [$field => $value]))->assertSessionHasErrors($field);
    }

    public function test_pagination_keeps_filters_and_orders_newest_first(): void
    {
        Task::factory()->count(13)->create(['priority' => 'high']);
        $newest = Task::factory()->create(['title' => 'Newest task', 'priority' => 'high']);
        Task::factory()->create(['priority' => 'low']);

        $response = $this->get(route('tasks.index', ['status' => 'pending', 'priority' => 'high']))->assertOk();

        $this->assertSame(14, $response['tasks']->total());
        $this->assertSame(12, $response['tasks']->count());
        $this->assertSame($newest->id, $response['tasks']->first()->id);
        $this->assertStringContainsString('status=pending', $response['tasks']->nextPageUrl());
        $this->assertStringContainsString('priority=high', $response['tasks']->nextPageUrl());

        $this->get(route('tasks.index', ['status' => 'pending', 'priority' => 'high', 'page' => 2]))->assertOk()
            ->assertViewHas('tasks', fn ($tasks) => $tasks->count() === 2);
    }

    public function test_task_content_is_escaped_on_dashboard_details_and_edit_form(): void
    {
        $task = Task::factory()->create(['title' => '<script>alert(1)</script>', 'description' => '<img src=x onerror=alert(2)>']);

        foreach (['tasks.index', 'tasks.show', 'tasks.edit'] as $route) {
            $this->get(route($route, $route === 'tasks.index' ? [] : $task))->assertOk()
                ->assertSee($task->title)->assertSee($task->description)
                ->assertDontSee($task->title, false)->assertDontSee($task->description, false);
        }
    }

    #[TestWith(['release', ['Prepare Release', 'Write notes']])]
    #[TestWith(['0', ['Version 0']])]
    #[TestWith(['missing', []])]
    #[TestWith(["' OR 1=1 --", []])]
    public function test_search_matches_title_or_description_without_changing_statistics(string $search, array $titles): void
    {
        Task::factory()->create(['title' => 'Prepare Release', 'description' => null]);
        Task::factory()->completed()->create(['title' => 'Write notes', 'description' => 'Document the RELEASE changes']);
        Task::factory()->create(['title' => 'Version 0', 'description' => null]);

        $response = $this->get(route('dashboard', compact('search')))->assertOk()
            ->assertViewHas('stats', ['total' => 3, 'completed' => 1, 'pending' => 2, 'percentage' => 33]);

        $this->assertSame($titles, $response['tasks']->pluck('title')->sort()->values()->all());
    }

    #[TestWith([''])]
    #[TestWith(['   '])]
    public function test_blank_search_returns_all_tasks(string $search): void
    {
        Task::factory()->count(2)->create();

        $response = $this->get(route('tasks.index', compact('search')))->assertOk();

        $this->assertSame(2, $response['tasks']->total());
    }

    #[TestWith(['newest', ['Zebra', 'beta', 'Alpha']])]
    #[TestWith(['oldest', ['Alpha', 'beta', 'Zebra']])]
    #[TestWith(['priority', ['beta', 'Alpha', 'Zebra']])]
    #[TestWith(['alphabetical', ['Alpha', 'beta', 'Zebra']])]
    public function test_sort_options_return_tasks_in_the_selected_order(string $sort, array $titles): void
    {
        Task::factory()->create(['title' => 'Zebra', 'priority' => 'low', 'created_at' => '2026-01-03 12:00:00']);
        Task::factory()->create(['title' => 'Alpha', 'priority' => 'medium', 'created_at' => '2026-01-01 12:00:00']);
        Task::factory()->create(['title' => 'beta', 'priority' => 'high', 'created_at' => '2026-01-02 12:00:00']);

        $response = $this->get(route('tasks.index', compact('sort')))->assertOk();

        $this->assertSame($titles, $response['tasks']->pluck('title')->all());
    }

    #[TestWith(['newest', ['Zebra release', 'Alpha release']])]
    #[TestWith(['oldest', ['Alpha release', 'Zebra release']])]
    #[TestWith(['priority', ['Zebra release', 'Alpha release']])]
    #[TestWith(['alphabetical', ['Alpha release', 'Zebra release']])]
    public function test_search_and_sort_respect_both_status_and_priority_filters(string $sort, array $titles): void
    {
        Task::factory()->create(['title' => 'Alpha release', 'priority' => 'high', 'created_at' => '2026-01-01 12:00:00']);
        Task::factory()->create(['title' => 'Zebra release', 'priority' => 'high', 'created_at' => '2026-01-02 12:00:00']);
        Task::factory()->completed()->create(['title' => 'Completed release', 'description' => 'release', 'priority' => 'high']);
        Task::factory()->create(['title' => 'Low release', 'description' => 'release', 'priority' => 'low']);
        Task::factory()->create(['title' => 'Unrelated', 'description' => null, 'priority' => 'high']);

        $response = $this->get(route('tasks.index', [
            'search' => 'release', 'status' => 'pending', 'priority' => 'high', 'sort' => $sort,
        ]))->assertOk();

        $this->assertSame($titles, $response['tasks']->pluck('title')->all());
    }

    public function test_sort_form_and_pagination_preserve_search_and_selected_filters(): void
    {
        Task::factory()->completed()->count(13)->create(['title' => 'Release task', 'priority' => 'high']);
        $filters = ['search' => 'Release', 'status' => 'completed', 'priority' => 'high', 'sort' => 'oldest'];

        $response = $this->get(route('tasks.index', $filters))->assertOk()
            ->assertSee('name="search" value="Release"', false)
            ->assertSee('value="completed" selected', false)
            ->assertSee('value="high" selected', false)
            ->assertSee('value="oldest" selected', false);

        $this->assertSame(13, $response['tasks']->total());
        $this->assertSame(12, $response['tasks']->count());
        parse_str(parse_url($response['tasks']->nextPageUrl(), PHP_URL_QUERY), $nextPageFilters);
        $this->assertSame([...$filters, 'page' => '2'], $nextPageFilters);

        $nextPage = $this->get($response['tasks']->nextPageUrl())->assertOk();

        $this->assertSame(1, $nextPage['tasks']->count());
        $this->assertSame(13, $nextPage['tasks']->first()->id);
    }

    #[TestWith(['search', ['release'], 'The search field must be a string.'])]
    #[TestWith(['sort', ['oldest'], 'The sort field must be a string.'])]
    #[TestWith(['sort', 'title desc', 'The selected sort is invalid.'])]
    public function test_invalid_search_and_sort_values_are_rejected(string $field, mixed $value, string $message): void
    {
        $this->get(route('tasks.index', [$field => $value]))->assertSessionHasErrors([$field => $message]);
    }

    public function test_search_length_is_limited(): void
    {
        $this->get(route('tasks.index', ['search' => str_repeat('a', 256)]))
            ->assertSessionHasErrors(['search' => 'The search field must not be greater than 255 characters.']);
    }

    public function test_search_input_is_escaped_in_the_dashboard_form(): void
    {
        $search = '\"><script>alert(1)</script>';

        $this->get(route('tasks.index', compact('search')))->assertOk()
            ->assertSee($search)->assertDontSee($search, false);
    }

    public function test_unknown_tasks_return_not_found_for_all_record_operations(): void
    {
        $this->get(route('tasks.show', 999))->assertNotFound();
        $this->get(route('tasks.edit', 999))->assertNotFound();
        $this->put(route('tasks.update', 999), ['title' => 'Valid', 'priority' => 'low'])->assertNotFound();
        $this->patch(route('tasks.completion', 999), ['is_completed' => true])->assertNotFound();
        $this->delete(route('tasks.destroy', 999))->assertNotFound();
    }
}
