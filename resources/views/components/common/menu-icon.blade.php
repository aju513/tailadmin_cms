@props([
    'name' => 'dashboard',
])

@php
    $paths = [
        'hall' => '<path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M8 9h1m6 0h1M8 12h1m6 0h1"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'content' => '<path d="M4 5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/><path d="M8 7h8M8 11h8M8 15h5"/>',
        'pages' => '<path d="M6 3h9l3 3v15H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path d="M14 3v4h4M8 12h8M8 16h6"/>',
        'categories' => '<path d="M4 5a2 2 0 0 1 2-2h5l2 3h5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5Z"/>',
        'menus' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'slides' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8" cy="9" r="1.5"/><path d="m4 17 5-5 3 3 2-2 6 5"/>',
        'media' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'tags' => '<path d="M20 13 13 20a2 2 0 0 1-3 0L4 14V4h10l6 6a2 2 0 0 1 0 3Z"/><circle cx="8.5" cy="8.5" r="1"/>',
        'authors' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'settings' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="m19.4 15 .1.1 1.4 1.4-1.4 1.4-.1-.1a1.7 1.7 0 0 0-2.8 1.2v.1h-2v-.1a1.7 1.7 0 0 0-2.6-1.5 1.7 1.7 0 0 0-.3.3l-.1.1-1.4-1.4.1-.1A1.7 1.7 0 0 0 9 13.9H8v-2h1a1.7 1.7 0 0 0 1.2-2.8l-.1-.1 1.4-1.4.1.1A1.7 1.7 0 0 0 14.1 9h.1V7h2v2.1a1.7 1.7 0 0 0 2.8 1.2l.1-.1 1.4 1.4-.1.1A1.7 1.7 0 0 0 19.4 15Z"/>',
        'access-control' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-4-4h-1M16 3.13a4 4 0 0 1 0 7.75"/>',
        'roles' => '<path d="M12 3 4 6v5c0 5.25 3.42 8.78 8 10 4.58-1.22 8-4.75 8-10V6l-8-3Z"/><path d="m9 12 2 2 4-4"/>',
        'permissions' => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m11 12 9-9m-3 3 3 3m-6 0 3 3"/>',
        'system' => '<path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06-1.42 1.42-.06-.06A1.65 1.65 0 0 0 16.5 18a1.65 1.65 0 0 0-1 1.5V20h-2v-.5a1.65 1.65 0 0 0-1-1.5 1.65 1.65 0 0 0-1.82.33l-.06.06-1.42-1.42.06-.06A1.65 1.65 0 0 0 9.5 15a1.65 1.65 0 0 0-1.5-1H7v-2h1a1.65 1.65 0 0 0 1.5-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06 1.42-1.42.06.06A1.65 1.65 0 0 0 12.5 8a1.65 1.65 0 0 0 1-1.5V6h2v.5a1.65 1.65 0 0 0 1 1.5 1.65 1.65 0 0 0 1.82-.33l.06-.06 1.42 1.42-.06.06A1.65 1.65 0 0 0 19.5 11a1.65 1.65 0 0 0 1.5 1h1v2h-1a1.65 1.65 0 0 0-1.6 1Z"/>',
        'activity-log' => '<path d="M6 2h9l3 3v17H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Z"/><path d="M14 2v4h4M8 11h8M8 15h8M8 19h5"/>',
        'ui-kit' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'create' => '<path d="M12 5v14M5 12h14"/>',
        'delete' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 14h10l1-14M9 7V4h6v3"/>',
        'activate' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/>',
        'deactivate' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/>',
        'view' => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4 11.5-11.5Z"/>',
    ];

    $icon = $paths[$name] ?? $paths['dashboard'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0', 'viewBox' => '0 0 24 24', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '1.7', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    {!! $icon !!}
</svg>
