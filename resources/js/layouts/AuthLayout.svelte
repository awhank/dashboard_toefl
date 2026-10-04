<script lang="ts">
    import type { Snippet } from 'svelte';

    let { children }: { children?: Snippet } = $props();
</script>

<div class="flex min-h-screen items-center justify-center bg-muted/40 px-4 py-10">
    <div class="w-full max-w-sm">
        <div class="mb-6 flex justify-center">
            <img src="/logo_polkessin.png" alt="Poltekkes Banjarmasin" class="h-12 w-auto" />
        </div>
        <div class="rounded-xl border border-border bg-white p-6 shadow-sm">
            {@render children?.()}
        </div>
        <p class="mt-4 text-center text-xs text-muted-foreground">Dashboard TOEFL Poltekkes Banjarmasin</p>
    </div>
</div>
