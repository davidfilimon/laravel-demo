@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <h1 class="text-3xl font-semibold tracking-tight">Your team<span class="text-violet-400">.</span></h1>
    <p class="mt-3 mb-8 text-sm text-zinc-400">Create accounts, then assign tasks from the dashboard.</p>
    <div class="grid items-start gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-line bg-panel p-6" aria-labelledby="users-title">
            <h2 id="users-title" class="mb-5 text-lg font-medium">Workspace users</h2>
            <div class="divide-y divide-line">
                @foreach($users as $user)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <div class="min-w-0"><p class="wrap-anywhere text-sm font-medium">{{ $user->name }} @if($user->is_admin)<span class="ml-2 text-xs text-violet-300">Admin</span>@endif</p><p class="mt-1 wrap-anywhere text-xs text-zinc-500">{{ $user->email }}</p></div>
                        <a href="{{ route('tasks.index', ['user_id' => $user->id]) }}" class="text-xs text-violet-300 hover:text-violet-200">{{ $user->tasks_count }} tasks <span aria-hidden="true">→</span></a>
                    </div>
                @endforeach
            </div>
            <div class="mt-5">{{ $users->links() }}</div>
        </section>
        <section class="rounded-xl border border-line bg-panel p-6" aria-labelledby="create-user-title">
            <h2 id="create-user-title" class="mb-5 text-lg font-medium">Create user</h2>
            <form method="POST" action="{{ route('users.store') }}" class="flex flex-col gap-5">
                @csrf
                @if($errors->any())<div role="alert" class="text-sm text-rose-300">Please check the highlighted fields.</div>@endif
                @foreach(['name' => 'Name', 'email' => 'Email', 'password' => 'Password', 'password_confirmation' => 'Confirm password'] as $field => $label)
                    <div>
                        <label for="{{ $field }}" class="field-label">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ str_starts_with($field, 'password') ? 'password' : ($field === 'email' ? 'email' : 'text') }}" @if(!str_starts_with($field, 'password')) value="{{ old($field) }}" maxlength="255" @else minlength="12" autocomplete="new-password" @endif class="field" required @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                        @error($field)<p id="{{ $field }}-error" class="field-error">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <p class="text-xs text-zinc-500">Use at least 12 characters. Share the credentials privately with the user.</p>
                <button type="submit" class="button-primary"><x-icon name="plus" class="size-4"/> Create user</button>
            </form>
        </section>
    </div>
@endsection
