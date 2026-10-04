<script lang="ts">
    import type { Snippet } from 'svelte';

    let {
        class: className = '',
        children,
    }: {
        class?: string;
        children?: Snippet;
    } = $props();
</script>

<div class="rounded-xl border border-border bg-white p-5 shadow-sm {className}">
    {@render children?.()}
</div>
