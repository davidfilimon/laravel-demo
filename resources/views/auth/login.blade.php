<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101014">
    <title>Sign in · Orbit Task Manager</title>
    {{ Vite::fonts() }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas px-5 py-12 font-sans text-zinc-100 antialiased">
    <main class="w-full max-w-md">
        <div class="mb-8 flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-xl bg-violet-500"><x-icon name="layers" class="size-6"/></span><span class="text-2xl font-semibold">orbit<span class="text-violet-400">.</span></span></div>
        <div class="rounded-2xl border border-line bg-panel p-7">
            <h1 class="text-2xl font-semibold tracking-tight">Welcome back.</h1>
            <p class="mt-3 text-sm text-zinc-400">Sign in to see your tasks and keep things moving.</p>
            <form method="POST" action="{{ route('login.store') }}" class="mt-7 flex flex-col gap-5">
                @csrf
                @if($errors->any())<p role="alert" class="field-error">{{ $errors->first() }}</p>@endif
                <div><label for="email" class="field-label">Email</label><input id="email" name="email" type="email" value="{{ old('email') }}" class="field" required autocomplete="username" autofocus></div>
                <div><label for="password" class="field-label">Password</label><input id="password" name="password" type="password" class="field" required autocomplete="current-password"></div>
                <button type="submit" class="button-primary">Sign in <x-icon name="arrow" class="size-4"/></button>
            </form>
        </div>
        <p class="mt-5 text-center text-xs text-zinc-500">Need an account? Ask your administrator.</p>
    </main>
</body>
</html>
