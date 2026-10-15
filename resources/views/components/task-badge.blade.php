@props(['priority'])
<span @class(['inline-flex items-center gap-1.5 shrink-0 rounded-lg px-2.5 py-1.5 text-xs font-medium ring-1 ring-inset', 'bg-rose-400/10 text-rose-300 ring-rose-400/25' => $priority === 'high', 'bg-amber-400/10 text-amber-200 ring-amber-400/25' => $priority === 'medium', 'bg-sky-400/10 text-sky-300 ring-sky-400/25' => $priority === 'low'])>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ ucfirst($priority) }} priority
</span>
