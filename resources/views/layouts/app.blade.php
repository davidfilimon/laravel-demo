<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101014">
    <title>@yield('title', 'Dashboard') · Orbit Task Manager</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-zinc-100 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-50 focus:rounded-lg focus:bg-violet-600 focus:p-3">Skip to content</a>
    <div class="min-h-screen lg:grid lg:grid-cols-[240px_minmax(0,1fr)]">
        <aside class="border-b border-line bg-sidebar lg:sticky lg:top-0 lg:flex lg:h-screen lg:flex-col lg:border-r lg:border-b-0">
            <div class="flex items-center justify-between px-6 py-7 lg:px-7 lg:py-9">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3" aria-label="Orbit home">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-violet-500 text-white shadow-lg shadow-violet-500/20"><x-icon name="layers" class="size-6"/></span>
                    <span class="text-2xl font-semibold tracking-tight">orbit<span class="text-violet-400">.</span></span>
                </a>
                <button type="button" class="icon-button lg:hidden" data-menu-toggle aria-expanded="false" aria-controls="workspace-nav" aria-label="Toggle navigation"><x-icon name="menu"/></button>
            </div>
            <nav id="workspace-nav" class="hidden flex-1 px-4 pb-5 lg:block" aria-label="Workspace navigation">
                <p class="px-3 pt-3 pb-4 text-[10px] font-semibold tracking-[0.18em] text-zinc-500">WORKSPACE</p>
                @php($activeStatus = request('status'))
                <div class="flex flex-col gap-1.5">
                    <a href="{{ route('tasks.index') }}" @class(['nav-link', 'nav-active' => !$activeStatus && !request('priority') && (request()->routeIs('tasks.index') || request()->routeIs('dashboard'))]) @if(!$activeStatus && !request('priority') && (request()->routeIs('tasks.index') || request()->routeIs('dashboard'))) aria-current="page" @endif><x-icon name="grid"/> All tasks @isset($stats)<span class="ml-auto rounded-md bg-white/5 px-2 py-0.5 text-xs">{{ $stats['total'] }}</span>@endisset</a>
                    <a href="{{ route('tasks.index', ['status' => 'pending']) }}" @class(['nav-link', 'nav-active' => $activeStatus === 'pending']) @if($activeStatus === 'pending') aria-current="page" @endif><x-icon name="clock"/> Pending @isset($stats)<span class="ml-auto text-xs">{{ $stats['pending'] }}</span>@endisset</a>
                    <a href="{{ route('tasks.index', ['status' => 'completed']) }}" @class(['nav-link', 'nav-active' => $activeStatus === 'completed']) @if($activeStatus === 'completed') aria-current="page" @endif><x-icon name="circle-check"/> Completed @isset($stats)<span class="ml-auto text-xs">{{ $stats['completed'] }}</span>@endisset</a>
                </div>
                <p class="px-3 pt-9 pb-4 text-[10px] font-semibold tracking-[0.18em] text-zinc-500">YOUR FOCUS</p>
                <a href="{{ route('tasks.index', ['status' => 'pending', 'priority' => 'high']) }}" class="nav-link"><x-icon name="flag" class="text-rose-300"/> High priority</a>
                <a href="{{ route('tasks.create') }}" class="nav-link"><x-icon name="plus"/> Create a task</a>
                @can('manage-users')
                    <a href="{{ route('users.index') }}" class="nav-link"><x-icon name="layers"/> Manage users</a>
                @endcan
                <form method="POST" action="{{ route('logout') }}" class="mt-5">@csrf<button type="submit" class="nav-link w-full"><x-icon name="back"/> Sign out</button></form>
            </nav>
            <div class="hidden px-5 pb-6 lg:block">
                <div class="rounded-xl border border-violet-400/15 bg-linear-to-br from-violet-500/10 to-transparent p-4">
                    <x-icon name="sparkles" class="mb-3 text-violet-300"/>
                    <p class="text-sm font-medium">Small steps. Big things.</p>
                    <p class="mt-2 text-xs leading-relaxed text-zinc-400">Make space for your next idea. One task at a time.</p>
                </div>
                <div class="mt-6 flex items-center gap-3 border-t border-line pt-5">
                    <span class="flex size-9 items-center justify-center rounded-lg border border-line bg-white/5 text-xs font-medium">OW</span>
                    <div><p class="text-xs font-medium">{{ auth()->user()->name }}</p><p class="mt-1 flex items-center gap-1.5 text-[10px] text-zinc-500"><span class="size-1.5 rounded-full bg-emerald-400"></span> {{ auth()->user()->is_admin ? 'Administrator' : 'Personal tasks' }}</p></div>
                </div>
            </div>
        </aside>
        <div class="min-w-0">
            <header class="flex min-h-20 items-center justify-between gap-3 border-b border-line px-5 sm:px-9 xl:px-12">
                <div class="flex items-center gap-2 text-xs"><span class="text-zinc-500">Workspace</span><span class="text-zinc-600">/</span><span>@yield('title', 'Dashboard')</span></div>
                <span class="flex items-center gap-2 text-xs text-zinc-400"><x-icon name="calendar" class="size-4"/><time datetime="{{ now()->toDateString() }}">{{ now()->format('M j, Y') }}</time></span>
            </header>
            <main id="main" class="mx-auto max-w-[1500px] px-5 py-8 sm:px-9 sm:py-10 xl:px-12">
                @if(session('success'))
                    <div role="status" class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-400/20 bg-emerald-400/5 p-4 text-sm text-emerald-200"><x-icon name="circle-check"/>{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <dialog id="delete-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-panel p-7 text-zinc-100 shadow-2xl backdrop:bg-black/75" aria-labelledby="delete-title" aria-describedby="delete-description">
        <span class="mb-5 flex size-12 items-center justify-center rounded-xl bg-rose-400/10 text-rose-300"><x-icon name="trash" class="size-6"/></span>
        <h2 id="delete-title" class="text-xl font-semibold">Delete this task?</h2>
        <p id="delete-description" class="mt-3 text-sm leading-relaxed text-zinc-400">This will permanently remove the task. You can’t undo this action.</p>
        <div class="mt-7 flex justify-end gap-3"><button type="button" class="button-secondary" data-delete-cancel>Keep task</button><button type="button" class="button-danger" data-delete-confirm>Delete task</button></div>
    </dialog>
</body>
</html>
