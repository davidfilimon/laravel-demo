@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="mb-7 flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-center">
        <div><p class="mb-2 text-xs font-medium tracking-wide text-violet-300">A CLEAR SPACE. A FOCUSED MIND.</p><h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">Your task dashboard<span class="text-violet-400">.</span></h1><p class="mt-3 text-sm text-zinc-400">Keep track of what matters, and make a little progress every day.</p></div>
        <a href="{{ route('tasks.create') }}" class="button-primary w-full shrink-0 sm:w-auto"><x-icon name="plus" class="size-4"/> New task</a>
    </div>

    <section class="hero-panel relative mb-5 overflow-hidden rounded-2xl border border-violet-400/20 p-5 sm:p-7" aria-label="Workspace progress">
        <div class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center">
            <div><span class="mb-3 inline-flex items-center gap-2 text-xs font-medium text-violet-300"><x-icon name="sparkles" class="size-4"/> Find your flow</span><h2 class="text-2xl font-medium tracking-tight sm:text-3xl">A little focus.<br>A lot of progress.</h2><p class="mt-3 max-w-sm text-sm leading-relaxed text-zinc-400">Every checked box is a step forward.<br>You’ve got this, one task at a time.</p></div>
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="relative flex size-24 shrink-0 sm:size-28 items-center justify-center">
                    <svg viewBox="0 0 100 100" class="absolute size-full -rotate-90" aria-hidden="true"><circle cx="50" cy="50" r="43" fill="none" stroke="#ffffff12" stroke-width="5"/><circle cx="50" cy="50" r="43" fill="none" stroke="#a78bfa" stroke-width="5" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $stats['percentage'] }} 100"/></svg>
                    <span class="text-2xl font-semibold tracking-tight">{{ $stats['percentage'] }}<span class="text-sm text-violet-300">%</span></span>
                </div>
                <div><p class="text-sm font-medium">Your overall progress</p><p class="mt-2 text-xs text-zinc-400">{{ $stats['completed'] }} of {{ $stats['total'] }} tasks completed</p><p class="mt-3 text-xs text-violet-300">{{ $stats['total'] === 0 ? 'Your next chapter starts here.' : ($stats['pending'] === 0 ? 'All done. Enjoy the moment.' : 'Keep the momentum going.') }}</p></div>
            </div>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-2 gap-3 xl:grid-cols-4 xl:gap-4" aria-label="Task statistics">
        @foreach([['label' => 'Total tasks', 'value' => $stats['total'], 'icon' => 'layers', 'color' => 'text-violet-300 bg-violet-400/10', 'note' => 'Everything in one place'], ['label' => 'Completed', 'value' => $stats['completed'], 'icon' => 'circle-check', 'color' => 'text-emerald-300 bg-emerald-400/10', 'note' => 'A little win, every time'], ['label' => 'Pending', 'value' => $stats['pending'], 'icon' => 'clock', 'color' => 'text-amber-200 bg-amber-400/10', 'note' => 'Ready for your attention'], ['label' => 'Completion rate', 'value' => $stats['percentage'].'%', 'icon' => 'sparkles', 'color' => 'text-fuchsia-300 bg-fuchsia-400/10', 'note' => 'Progress you can see']] as $stat)
            <div class="stat-card p-4 sm:p-5"><div class="mb-4 flex items-center justify-between gap-2"><p class="text-xs text-zinc-400 sm:text-sm">{{ $stat['label'] }}</p><span class="flex size-8 items-center justify-center rounded-lg {{ $stat['color'] }}"><x-icon :name="$stat['icon']" class="size-4"/></span></div><p class="text-3xl font-semibold tracking-tight tabular-nums">{{ $stat['value'] }}</p><p class="mt-3 hidden text-[11px] text-zinc-400 sm:block">{{ $stat['note'] }}</p></div>
        @endforeach
    </section>

    <section aria-labelledby="tasks-heading">
        <div class="mb-5 flex items-center justify-between gap-3"><div class="flex items-center gap-3"><h2 id="tasks-heading" class="text-xl font-semibold tracking-tight">Your tasks</h2><span class="rounded-md border border-line bg-white/5 px-2 py-0.5 text-xs text-zinc-400">{{ $tasks->total() }}</span></div><span class="text-xs text-zinc-400">Newest first</span></div>
        <div class="surface-panel overflow-hidden">
            <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-2 items-end gap-3 border-b border-line bg-white/[0.015] p-4 sm:flex sm:flex-wrap sm:p-5" aria-label="Filter tasks">
                <span class="hidden self-center text-zinc-400 sm:block"><x-icon name="filter"/></span>
                <div class="min-w-0 sm:min-w-32 sm:flex-1 sm:max-w-44"><label for="status" class="mb-1.5 block text-[11px] text-zinc-400">Status</label><select id="status" name="status" class="field text-xs"><option value="">All statuses</option><option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option><option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option></select></div>
                <div class="min-w-0 sm:min-w-32 sm:flex-1 sm:max-w-44"><label for="priority" class="mb-1.5 block text-[11px] text-zinc-400">Priority</label><select id="priority" name="priority" class="field text-xs"><option value="">All priorities</option>@foreach(\App\Models\Task::PRIORITIES as $priority)<option value="{{ $priority }}" @selected(($filters['priority'] ?? '') === $priority)>{{ ucfirst($priority) }} priority</option>@endforeach</select></div>
                <button type="submit" class="button-secondary col-span-2 sm:w-auto">Apply filters</button>
                @if(!empty($filters['status']) || !empty($filters['priority']))<a href="{{ route('tasks.index') }}" class="col-span-2 rounded-lg px-2 py-2.5 text-center text-sm text-violet-300 hover:text-violet-200">Clear filters</a>@endif
            </form>
            @if($errors->any())<div role="alert" class="border-b border-line p-4 text-sm text-rose-300">{{ $errors->first() }}</div>@endif
            <div class="hidden grid-cols-[minmax(0,1fr)_140px_120px_96px] gap-6 border-b border-line px-6 py-3 text-[10px] font-semibold tracking-wider text-zinc-400 xl:grid"><span>TASK</span><span>PRIORITY</span><span>STATUS</span><span class="text-right">ACTIONS</span></div>
            <div class="task-list">
                @forelse($tasks as $task)
                    <article @class(['task-card grid gap-4 p-4 sm:p-5 xl:grid-cols-[minmax(0,1fr)_140px_120px_96px] xl:items-center xl:gap-6 xl:px-6', 'task-card-completed' => $task->is_completed])>
                        <div class="flex min-w-0 items-start gap-4">
                            <form action="{{ route('tasks.completion', $task) }}" method="POST" class="shrink-0">@csrf @method('PATCH')<input type="hidden" name="is_completed" value="{{ $task->is_completed ? 0 : 1 }}"><button type="submit" @class(['task-toggle', 'task-toggle-completed' => $task->is_completed, 'task-toggle-pending' => !$task->is_completed]) aria-pressed="{{ $task->is_completed ? 'true' : 'false' }}" aria-label="{{ $task->is_completed ? 'Mark pending:' : 'Complete:' }} {{ $task->title }}" title="{{ $task->is_completed ? 'Mark as pending' : 'Mark complete' }}">@if($task->is_completed)<x-icon name="check" class="size-5"/>@endif</button></form>
                            <div class="min-w-0"><a href="{{ route('tasks.show', $task) }}" @class(['task-title block rounded-sm wrap-anywhere text-sm font-medium leading-6 transition-colors hover:text-violet-300', 'text-zinc-400 line-through decoration-zinc-600' => $task->is_completed])>{{ $task->title }}</a><p class="mt-1.5 line-clamp-1 wrap-anywhere text-xs text-zinc-400">{{ $task->description ?: 'No description added.' }}</p></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 border-t border-line/60 pt-3 sm:gap-3 xl:contents"><div><x-task-badge :priority="$task->priority"/></div><span @class(['task-status', 'bg-emerald-400/10 text-emerald-200' => $task->is_completed, 'bg-white/5 text-zinc-300' => !$task->is_completed])><span @class(['size-1.5 rounded-full', 'bg-emerald-400' => $task->is_completed, 'bg-zinc-500' => !$task->is_completed])></span>{{ $task->is_completed ? 'Completed' : 'Pending' }}</span><div class="ml-auto flex justify-end gap-1"><a href="{{ route('tasks.edit', $task) }}" class="icon-button" aria-label="Edit {{ $task->title }}" title="Edit task"><x-icon name="edit" class="size-4"/></a><form action="{{ route('tasks.destroy', $task) }}" method="POST" data-delete-form>@csrf @method('DELETE')<button type="submit" class="icon-button hover:text-rose-300" aria-label="Delete {{ $task->title }}" title="Delete task"><x-icon name="trash" class="size-4"/></button></form></div></div>
                    </article>
                @empty
                    <div class="flex flex-col items-center px-5 py-16 text-center"><div class="mb-6 flex size-16 rotate-[-8deg] items-center justify-center rounded-2xl border border-violet-400/20 bg-violet-400/10 shadow-xl shadow-violet-500/5"><x-icon name="layers" class="size-8 text-violet-300"/></div><h3 class="text-lg font-medium">{{ $stats['total'] === 0 ? 'A fresh start, just for you.' : 'Nothing here just yet.' }}</h3><p class="mt-2 max-w-sm text-sm leading-relaxed text-zinc-400">{{ $stats['total'] === 0 ? 'Turn that idea into your first task. Big things start with one small step.' : 'No tasks match these filters. Try another combination or create something new.' }}</p><a href="{{ $stats['total'] === 0 ? route('tasks.create') : route('tasks.index') }}" class="button-primary mt-6"><x-icon :name="$stats['total'] === 0 ? 'plus' : 'back'" class="size-4"/>{{ $stats['total'] === 0 ? 'Create your first task' : 'View all tasks' }}</a></div>
                @endforelse
            </div>
            @if($tasks->total() > 0)
                <div class="flex flex-col items-start justify-between gap-4 border-t border-line px-4 py-5 sm:flex-row sm:items-center sm:px-6"><p class="text-xs text-zinc-400">Showing {{ $tasks->firstItem() }}–{{ $tasks->lastItem() }} of {{ $tasks->total() }} tasks</p><nav class="flex items-center gap-2" aria-label="Task pagination">@if($tasks->onFirstPage())<span class="pagination-disabled">Previous</span>@else<a href="{{ $tasks->previousPageUrl() }}" class="button-secondary">Previous</a>@endif<span class="px-2 text-xs text-zinc-400">{{ $tasks->currentPage() }} / {{ $tasks->lastPage() }}</span>@if($tasks->hasMorePages())<a href="{{ $tasks->nextPageUrl() }}" class="button-secondary">Next</a>@else<span class="pagination-disabled">Next</span>@endif</nav></div>
            @endif
        </div>
        <p class="mt-5 flex items-center justify-center gap-2 text-[11px] text-zinc-400"><x-icon name="circle-check" class="size-3.5"/> One task at a time. You’re making progress.</p>
    </section>
@endsection
