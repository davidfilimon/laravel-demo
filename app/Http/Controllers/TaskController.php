<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $sortOptions = [
            'newest' => 'Newest first',
            'oldest' => 'Oldest first',
            'priority' => 'Priority (High to Low)',
            'alphabetical' => 'Alphabetical (A-Z)',
        ];

        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'completed'])],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(array_keys($sortOptions))],
            'user_id' => $request->user()->is_admin ? ['nullable', 'integer', Rule::exists('users', 'id')] : ['prohibited'],
        ]);
        $filters['sort'] = $filters['sort'] ?? 'newest';

        $visibleTasks = Task::visibleTo($request->user());
        $query = (clone $visibleTasks)->with('user:id,name')
            ->when($filters['status'] ?? null, fn (Builder $query, string $status): Builder => $query->where('is_completed', $status === 'completed'))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $priority): Builder => $query->where('priority', $priority))
            ->when($request->user()->is_admin && ($filters['user_id'] ?? null), fn (Builder $query): Builder => $query->where('user_id', $filters['user_id']));

        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function (Builder $query) use ($filters): void {
                $query->whereLike('title', '%'.$filters['search'].'%')
                    ->orWhereLike('description', '%'.$filters['search'].'%');
            });
        }

        match ($filters['sort']) {
            'oldest' => $query->oldest()->orderBy('id'),
            'priority' => $query->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
                ->latest()->orderByDesc('id'),
            'alphabetical' => $query->orderByRaw('LOWER(title)')->orderBy('id'),
            default => $query->latest()->orderByDesc('id'),
        };

        $tasks = $query->paginate(12)->withQueryString();

        $total = (clone $visibleTasks)->count();
        $completed = (clone $visibleTasks)->where('is_completed', true)->count();
        $stats = [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'percentage' => $total === 0 ? 0 : (int) round($completed / $total * 100),
        ];

        return view('tasks.index', compact('tasks', 'stats', 'filters', 'sortOptions') + ['users' => $request->user()->is_admin ? User::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function create(Request $request): View
    {
        return view('tasks.form', ['task' => new Task, 'users' => $request->user()->is_admin ? User::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function store(SaveTaskRequest $request): RedirectResponse
    {
        Task::create([
            ...$request->validated(),
            'is_completed' => $request->boolean('is_completed'),
            'user_id' => $request->user()->is_admin
                ? ($request->validated('user_id') ?? $request->user()->id)
                : $request->user()->id,
        ]);

        return to_route('tasks.index')->with('success', 'Task created. Let’s make it happen.');
    }

    public function show(Task $task): View
    {
        Gate::authorize('view', $task);

        return view('tasks.show', compact('task'));
    }

    public function edit(Request $request, Task $task): View
    {
        Gate::authorize('update', $task);

        return view('tasks.form', compact('task') + ['users' => $request->user()->is_admin ? User::orderBy('name')->get(['id', 'name']) : collect()]);
    }

    public function update(SaveTaskRequest $request, Task $task): RedirectResponse
    {
        $data = $request->user()->is_admin
            ? $request->validated()
            : $request->safe()->except(['user_id']);
        $task->update([...$data, 'is_completed' => $request->boolean('is_completed')]);

        return to_route('tasks.index')->with('success', 'Task updated. You’re all set.');
    }

    public function completion(Request $request, Task $task): RedirectResponse
    {
        Gate::authorize('update', $task);

        $data = $request->validate(['is_completed' => ['required', 'boolean']]);
        $task->update($data);

        return back()->with('success', $task->is_completed ? 'Task completed. Nice work!' : 'Task moved back to pending.');
    }

    public function clearCompleted(Request $request): RedirectResponse
    {
        Task::visibleTo($request->user())->where('is_completed', true)->delete();

        return to_route('tasks.index')->with('success', 'Completed tasks cleared.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return to_route('tasks.index')->with('success', 'Task deleted. Space for what matters.');
    }
}
