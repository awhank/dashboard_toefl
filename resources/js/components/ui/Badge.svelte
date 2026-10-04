<script lang="ts">
    import type { Snippet } from 'svelte';

    type Tone = 'ok' | 'warning' | 'danger' | 'muted' | 'primary';

    let {
        tone = 'muted',
        children,
    }: {
        tone?: Tone;
        children?: Snippet;
    } = $props();

    const toneClasses: Record<Tone, string> = {
        ok: 'bg-status-ok-bg text-status-ok',
        warning: 'bg-status-warning-bg text-status-warning',
        danger: 'bg-status-danger-bg text-status-danger',
        muted: 'bg-status-muted-bg text-status-muted',
        primary: 'bg-brand-teal/10 text-primary',
    };
</script>

<span
    class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-medium {toneClasses[tone]}"
>
    {@render children?.()}
</span>
