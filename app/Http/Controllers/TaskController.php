<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTaskRequest;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'completed'])],
            'priority' => ['nullable', Rule::in(Task::PRIORITIES)],
        ]);

        $tasks = Task::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_completed', $status === 'completed'))
            ->when($filters['priority'] ?? null, fn ($query, $priority) => $query->where('priority', $priority))
            ->latest()->orderByDesc('id')
            ->paginate(12)->withQueryString();

        $total = Task::count();
        $completed = Task::where('is_completed', true)->count();
        $stats = [
            'total' => $total,
            'completed' => $completed,
            'pending' => $total - $completed,
            'percentage' => $total === 0 ? 0 : (int) round($completed / $total * 100),
        ];

        return view('tasks.index', compact('tasks', 'stats', 'filters'));
    }

    public function create(): View
    {
        return view('tasks.form', ['task' => new Task]);
    }

    public function store(SaveTaskRequest $request): RedirectResponse
    {
        Task::create([...$request->validated(), 'is_completed' => $request->boolean('is_completed')]);

        return to_route('tasks.index')->with('success', 'Task created. Let’s make it happen.');
    }

    public function show(Task $task): View
    {
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        return view('tasks.form', compact('task'));
    }

    public function update(SaveTaskRequest $request, Task $task): RedirectResponse
    {
        $task->update([...$request->validated(), 'is_completed' => $request->boolean('is_completed')]);

        return to_route('tasks.index')->with('success', 'Task updated. You’re all set.');
    }

    public function completion(Request $request, Task $task): RedirectResponse
    {
        $data = $request->validate(['is_completed' => ['required', 'boolean']]);
        $task->update($data);

        return back()->with('success', $task->is_completed ? 'Task completed. Nice work!' : 'Task moved back to pending.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return to_route('tasks.index')->with('success', 'Task deleted. Space for what matters.');
    }
}
