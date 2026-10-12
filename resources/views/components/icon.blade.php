@props(['name'])
<svg {{ $attributes->class(['size-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('grid') <rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('circle-check') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
        @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
        @case('flag') <path d="M5 21V4m0 0c4-4 10 4 14 0v10c-4 4-10-4-14 0"/> @break
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('arrow') <path d="M5 12h14m-6-6 6 6-6 6"/> @break
        @case('back') <path d="M19 12H5m6-6-6 6 6 6"/> @break
        @case('edit') <path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15z"/> @break
        @case('trash') <path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/> @break
        @case('sparkles') <path d="m12 3 2.5 6.5L21 12l-6.5 2.5L12 21l-2.5-6.5L3 12l6.5-2.5zM21 2v4m-2-2h4"/> @break
        @case('layers') <path d="m12 3 10 5-10 5L2 8zM2 12l10 5 10-5M2 16l10 5 10-5"/> @break
        @case('calendar') <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18"/> @break
        @case('filter') <path d="M4 7h16M7 12h10M10 17h4"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
    @endswitch
</svg>
