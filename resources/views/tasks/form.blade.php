@extends('layouts.app')
@section('title', $task->exists ? 'Edit task' : 'New task')
@section('content')
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('tasks.index') }}" class="mb-7 inline-flex items-center gap-2 text-sm text-zinc-400 hover:text-violet-300"><x-icon name="back" class="size-4"/> Back to dashboard</a>
        <p class="mb-2 text-xs font-medium tracking-wide text-violet-300">MAKE ROOM FOR WHAT MATTERS</p>
        <h1 class="text-3xl font-semibold tracking-tight">{{ $task->exists ? 'Refine your next step' : 'Start something new' }}<span class="text-violet-400">.</span></h1>
        <p class="mt-3 mb-8 text-sm text-zinc-400">{{ $task->exists ? 'Update the details and keep things moving.' : 'Capture the idea. Set a priority. Take it one step at a time.' }}</p>
        <form action="{{ $task->exists ? route('tasks.update', $task) : route('tasks.store') }}" method="POST" class="rounded-2xl border border-line bg-panel p-5 sm:p-8">
            @csrf
            @if($task->exists) @method('PUT') @endif
            @if($errors->any())<div role="alert" class="mb-6 rounded-lg border border-rose-400/20 bg-rose-400/5 p-4 text-sm text-rose-300">Please check the highlighted fields and try again.</div>@endif
            <div class="flex flex-col gap-6">
                @if(auth()->user()->is_admin)
                    <div>
                        <label for="user_id" class="field-label">Assigned to <span class="text-violet-300">*</span></label>
                        <select id="user_id" name="user_id" class="field" required @error('user_id') aria-invalid="true" aria-describedby="user-error" @enderror>
                            @if($task->exists && !$task->user_id)<option value="">Unassigned — choose a user</option>@endif
                            @foreach($users as $user)<option value="{{ $user->id }}" @selected((string) old('user_id', $task->exists ? $task->user_id : auth()->id()) === (string) $user->id)>{{ $user->name }}</option>@endforeach
                        </select>
                        @error('user_id')<p id="user-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                @endif
                <div><label for="title" class="field-label">Task title <span class="text-violet-300">*</span></label><input type="text" name="title" id="title" value="{{ old('title', $task->title) }}" class="field" placeholder="What would you like to get done?" required maxlength="255" autofocus @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>@error('title')<p id="title-error" class="field-error">{{ $message }}</p>@enderror</div>
                <div><label for="description" class="field-label">Description <span class="ml-1 font-normal text-zinc-500">(optional)</span></label><textarea name="description" id="description" rows="5" maxlength="10000" class="field resize-y" placeholder="Add a little context, a few steps, or anything you’ll want to remember." @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $task->description) }}</textarea>@error('description')<p id="description-error" class="field-error">{{ $message }}</p>@enderror</div>
                <div><label for="priority" class="field-label">Priority <span class="text-violet-300">*</span></label><select name="priority" id="priority" class="field" required @error('priority') aria-invalid="true" aria-describedby="priority-error" @enderror>@foreach(\App\Models\Task::PRIORITIES as $priority)<option value="{{ $priority }}" @selected(old('priority', $task->priority ?? 'medium') === $priority)>{{ ucfirst($priority) }} priority</option>@endforeach</select>@error('priority')<p id="priority-error" class="field-error">{{ $message }}</p>@enderror<p class="mt-2 text-xs text-zinc-500">Give your most important work a little extra attention.</p></div>
                <div class="rounded-xl border border-line bg-white/[0.015] p-4"><input type="hidden" name="is_completed" value="0"><label for="is_completed" class="flex cursor-pointer items-center gap-3"><input type="checkbox" name="is_completed" id="is_completed" value="1" @checked(old('is_completed', $task->is_completed)) class="size-4 accent-violet-500" @error('is_completed') aria-invalid="true" aria-describedby="completion-error" @enderror><span><span class="block text-sm font-medium">Mark as completed</span><span class="mt-1 block text-xs text-zinc-500">Already done? Give yourself a little credit.</span></span></label>@error('is_completed')<p id="completion-error" class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="mt-8 flex flex-wrap justify-end gap-3 border-t border-line pt-6"><a href="{{ route('tasks.index') }}" class="button-secondary">Cancel</a><button type="submit" class="button-primary"><x-icon :name="$task->exists ? 'check' : 'plus'" class="size-4"/>{{ $task->exists ? 'Save changes' : 'Create task' }}</button></div>
        </form>
    </div>
@endsection
