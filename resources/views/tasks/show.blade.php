@extends('layouts.app')
@section('title', 'Task details')
@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('tasks.index') }}" class="mb-7 inline-flex items-center gap-2 text-sm rounded-sm text-zinc-400 transition-colors hover:text-violet-300"><x-icon name="back" class="size-4"/> Back to dashboard</a>
        <article class="surface-panel p-6 sm:p-9">
            <div class="mb-6 flex flex-wrap items-center gap-3"><x-task-badge :priority="$task->priority"/><span @class(['task-status', 'bg-emerald-400/10 text-emerald-200' => $task->is_completed, 'bg-white/5 text-zinc-300' => !$task->is_completed])><x-icon :name="$task->is_completed ? 'circle-check' : 'clock'" class="size-4"/>{{ $task->is_completed ? 'Completed' : 'Pending' }}</span></div>
            <h1 @class(['wrap-anywhere text-2xl font-semibold tracking-tight sm:text-3xl', 'text-zinc-300 line-through decoration-emerald-400/50' => $task->is_completed])>{{ $task->title }}</h1>
            <div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-xs text-zinc-400"><span>Created <time datetime="{{ $task->created_at->toIso8601String() }}">{{ $task->created_at->format('M j, Y · g:i a') }}</time></span><span>Updated <time datetime="{{ $task->updated_at->toIso8601String() }}">{{ $task->updated_at->format('M j, Y · g:i a') }}</time></span></div>
            <div class="mt-8 border-t border-line pt-7"><h2 class="mb-3 text-xs font-semibold tracking-wider text-zinc-400">DESCRIPTION</h2><p class="whitespace-pre-wrap wrap-anywhere text-sm leading-7 text-zinc-300">{{ $task->description ?: 'No description added. You can add details by editing this task.' }}</p></div>
            <div class="task-detail-actions mt-9 flex flex-col items-stretch gap-3 sm:flex-row sm:flex-wrap sm:items-center border-t border-line pt-6"><form action="{{ route('tasks.completion', $task) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="is_completed" value="{{ $task->is_completed ? 0 : 1 }}"><button type="submit" class="button-primary"><x-icon :name="$task->is_completed ? 'clock' : 'check'" class="size-4"/>{{ $task->is_completed ? 'Mark as pending' : 'Mark complete' }}</button></form><a href="{{ route('tasks.edit', $task) }}" class="button-secondary"><x-icon name="edit" class="size-4"/> Edit task</a><form action="{{ route('tasks.destroy', $task) }}" method="POST" class="sm:ml-auto" data-delete-form>@csrf @method('DELETE')<button type="submit" class="button-secondary text-rose-300 hover:border-rose-400/40"><x-icon name="trash" class="size-4"/> Delete</button></form></div>
        </article>
    </div>
@endsection
