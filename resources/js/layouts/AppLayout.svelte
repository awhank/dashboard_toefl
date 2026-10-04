<script lang="ts">
    import type { Snippet } from 'svelte';
    import Icon from '@/components/Icon.svelte';
    import { page, router } from '@inertiajs/svelte';
    import type { Role } from '@/types/auth';

    let {
        active = 'dashboard',
        role,
        userName,
        children,
        actions,
    }: {
        active?: string;
        role?: Role;
        userName?: string;
        children?: Snippet;
        actions?: Snippet;
    } = $props();

    // Nilai eksplisit lewat prop menang; kalau tidak diisi, ambil dari pengguna yang sedang login.
    const effectiveRole = $derived(role ?? page.props.auth?.user?.role ?? 'pegawai');
    const effectiveUserName = $derived(userName ?? page.props.auth?.user?.name ?? 'Pengguna');

    function logout() {
        router.post('/logout');
    }

    let collapsed = $state(false);
    let mobileOpen = $state(false);

    type NavItem = { key: string; label: string; icon: string; roles: Role[] };
    type NavGroup = { label?: string; items: NavItem[] };

    const groups: NavGroup[] = [
        {
            items: [
                { key: 'dashboard', label: 'Dashboard', icon: 'dashboard', roles: ['admin', 'pimpinan'] },
                { key: 'kandidat', label: 'Kandidat Peserta', icon: 'clipboard-list', roles: ['admin', 'pimpinan'] },
                { key: 'pegawai', label: 'Data Pegawai', icon: 'users', roles: ['admin', 'pimpinan'] },
                { key: 'periode', label: 'Periode Tes', icon: 'calendar', roles: ['admin', 'pimpinan'] },
            ],
        },
        {
            label: 'Master Data',
            items: [
                { key: 'jenis-tes', label: 'Jenis Tes', icon: 'tag', roles: ['admin'] },
                { key: 'target-skor', label: 'Target Skor', icon: 'target', roles: ['admin'] },
                { key: 'pengguna', label: 'Pengguna', icon: 'key', roles: ['admin'] },
            ],
        },
        {
            items: [{ key: 'profil', label: 'Profil', icon: 'user-circle', roles: ['admin', 'pimpinan', 'pegawai'] }],
        },
    ];

    const visibleGroups = $derived(
        groups
            .map((g) => ({ ...g, items: g.items.filter((i) => i.roles.includes(effectiveRole)) }))
            .filter((g) => g.items.length > 0),
    );

    const roleLabel: Record<Role, string> = {
        admin: 'Administrator',
        pimpinan: 'Pimpinan',
        pegawai: 'Pegawai',
    };
</script>

<div class="min-h-screen bg-muted/40 md:flex">
    <!-- Overlay mobile -->
    {#if mobileOpen}
        <button
            aria-label="Tutup menu"
            class="fixed inset-0 z-40 bg-black/30 md:hidden"
            onclick={() => (mobileOpen = false)}
        ></button>
    {/if}

    <!-- Sidebar -->
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col border-r border-border bg-sidebar transition-all duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0
        {collapsed ? 'md:w-[76px]' : 'md:w-[260px]'}
        {mobileOpen ? 'w-[260px] translate-x-0' : 'w-[260px] -translate-x-full md:translate-x-0'}"
    >
        <div class="flex h-16 shrink-0 items-center gap-2 border-b border-border px-4">
            {#if !collapsed}
                <img src="/logo_polkessin.png" alt="Poltekkes Banjarmasin" class="h-9 w-auto" />
            {:else}
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-md bg-primary font-heading text-sm text-primary-foreground"
                >
                    P
                </div>
            {/if}
        </div>

        {#if !collapsed}
            <div class="px-4 pt-3 pb-1">
                <p class="font-heading text-[15px] leading-tight text-foreground">Dashboard TOEFL</p>
                <p class="text-xs text-muted-foreground">Poltekkes Banjarmasin</p>
            </div>
        {/if}

        <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-3">
            {#each visibleGroups as group (group.label ?? group.items[0].key)}
                <div>
                    {#if group.label && !collapsed}
                        <p class="px-2 pb-1 text-[11px] font-semibold tracking-wide text-muted-foreground uppercase">
                            {group.label}
                        </p>
                    {/if}
                    <ul class="space-y-0.5">
                        {#each group.items as item (item.key)}
                            {@const isActive = active === item.key}
                            <li>
                                <a
                                    href="#{item.key}"
                                    title={collapsed ? item.label : undefined}
                                    class="group flex items-center gap-3 rounded-md px-2.5 py-2 text-sm transition-colors
                                    {isActive
                                        ? 'bg-brand-teal/10 font-medium text-primary'
                                        : 'text-foreground/80 hover:bg-muted'}
                                    {collapsed ? 'justify-center' : ''}"
                                    style={isActive ? 'box-shadow: inset 3px 0 0 var(--color-brand-teal);' : ''}
                                >
                                    <Icon
                                        name={item.icon}
                                        class="h-4.5 w-4.5 shrink-0 {isActive ? 'text-brand-teal' : 'text-muted-foreground'}"
                                    />
                                    {#if !collapsed}
                                        <span class="truncate">{item.label}</span>
                                    {/if}
                                </a>
                            </li>
                        {/each}
                    </ul>
                </div>
            {/each}
        </nav>

        <div class="border-t border-border p-3">
            <button
                onclick={logout}
                class="flex w-full items-center gap-3 rounded-md px-2.5 py-2 text-left text-sm text-foreground/80 hover:bg-muted {collapsed
                    ? 'justify-center'
                    : ''}"
            >
                <Icon name="log-out" class="h-4.5 w-4.5 shrink-0 text-muted-foreground" />
                {#if !collapsed}<span>Keluar</span>{/if}
            </button>
        </div>

        <!-- Toggle ciutkan (desktop) -->
        <button
            class="absolute top-16 -right-3 hidden h-6 w-6 items-center justify-center rounded-full border border-border bg-white text-muted-foreground shadow-sm hover:text-primary md:flex"
            onclick={() => (collapsed = !collapsed)}
            aria-label={collapsed ? 'Perluas sidebar' : 'Ciutkan sidebar'}
        >
            <Icon name={collapsed ? 'chevrons-right' : 'chevrons-left'} class="h-3.5 w-3.5" />
        </button>
    </aside>

    <!-- Main -->
    <div class="flex min-h-screen flex-1 flex-col">
        <header
            class="no-print sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-border bg-white px-4 md:px-6"
        >
            <button
                class="rounded-md p-2 text-muted-foreground hover:bg-muted md:hidden"
                onclick={() => (mobileOpen = true)}
                aria-label="Buka menu"
            >
                <Icon name="menu" class="h-5 w-5" />
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="font-heading truncate text-lg text-foreground md:text-xl">
                    {visibleGroups.flatMap((g) => g.items).find((i) => i.key === active)?.label ?? 'Dashboard'}
                </h1>
            </div>

            {#if actions}
                <div class="hidden items-center gap-2 sm:flex">{@render actions()}</div>
            {/if}

            <button class="rounded-md p-2 text-muted-foreground hover:bg-muted" aria-label="Notifikasi">
                <Icon name="bell" class="h-5 w-5" />
            </button>

            <div class="flex items-center gap-2 border-l border-border pl-3">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground"
                >
                    {effectiveUserName.charAt(0).toUpperCase()}
                </div>
                <div class="hidden leading-tight sm:block">
                    <p class="text-sm font-medium text-foreground">{effectiveUserName}</p>
                    <p class="text-xs text-muted-foreground">{roleLabel[effectiveRole]}</p>
                </div>
            </div>
        </header>

        <main class="print-area flex-1 p-4 md:p-6">
            {@render children?.()}
        </main>
    </div>
</div>
