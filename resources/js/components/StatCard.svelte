<script lang="ts">
    import Icon from '@/components/Icon.svelte';

    type Tone = 'primary' | 'ok' | 'warning' | 'danger' | 'muted';

    let {
        icon,
        label,
        value,
        hint = '',
        tone = 'primary',
    }: {
        icon: string;
        label: string;
        value: string | number;
        hint?: string;
        tone?: Tone;
    } = $props();

    const toneClasses: Record<Tone, string> = {
        primary: 'bg-brand-teal/10 text-primary',
        ok: 'bg-status-ok-bg text-status-ok',
        warning: 'bg-status-warning-bg text-status-warning',
        danger: 'bg-status-danger-bg text-status-danger',
        muted: 'bg-status-muted-bg text-status-muted',
    };
</script>

<div class="rounded-xl border border-border bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-muted-foreground">{label}</p>
            <p class="font-heading mt-1 text-3xl text-foreground">{value}</p>
            {#if hint}
                <p class="mt-1 text-xs text-muted-foreground">{hint}</p>
            {/if}
        </div>
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {toneClasses[tone]}">
            <Icon name={icon} class="h-5 w-5" />
        </div>
    </div>
</div>
