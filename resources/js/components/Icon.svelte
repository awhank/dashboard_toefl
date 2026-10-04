<script lang="ts">
    let { name, class: className = 'h-5 w-5' }: { name: string; class?: string } = $props();

    const paths: Record<string, string> = {
        dashboard: 'M3 3h7v9H3zM14 3h7v5h-7zM14 12h7v9h-7zM3 16h7v5H3z',
        'clipboard-list':
            'M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1zM6 5h12v15a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1zM9 10h6M9 13h6M9 16h4',
        users: 'M17 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M11 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
        calendar:
            'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z',
        tag: 'M20.59 13.41 11 3.83A2 2 0 0 0 9.59 3.24L4 3a1 1 0 0 0-1 1l.24 5.59a2 2 0 0 0 .59 1.41l9.58 9.59a2 2 0 0 0 2.83 0l4.35-4.35a2 2 0 0 0 0-2.83zM7 7h.01',
        target: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4z',
        key: 'M15.5 7.5a4.5 4.5 0 1 1-4.5-4.5M21 2l-9.6 9.6M15.5 7.5 18 5M18 5l1.5 1.5M15 8l2 2',
        'user-circle':
            'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM6.17 19a6 6 0 0 1 11.66 0',
        'log-out': 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
        'chevrons-left': 'M11 17l-5-5 5-5M18 17l-5-5 5-5',
        'chevrons-right': 'M13 17l5-5-5-5M6 17l5-5-5-5',
        menu: 'M3 12h18M3 6h18M3 18h18',
        printer:
            'M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z',
        bell: 'M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0',
        'chevron-down': 'M6 9l6 6 6-6',
        'alert-triangle':
            'M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0zM12 9v4M12 17h.01',
        'check-circle': 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3',
        clock: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20zM12 6v6l4 2',
    };
</script>

<svg
    class={className}
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
>
    <path d={paths[name] ?? ''} />
</svg>
