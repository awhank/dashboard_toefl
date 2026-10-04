<script lang="ts">
    type Bar = { label: string; value: number };

    let {
        bars,
        color = 'var(--color-brand-teal)',
        suffix = '',
    }: {
        bars: Bar[];
        color?: string;
        suffix?: string;
    } = $props();

    const max = $derived(Math.max(...bars.map((b) => b.value), 1));
</script>

<div class="flex h-40 items-end gap-4">
    {#each bars as bar (bar.label)}
        {@const heightPct = Math.max((bar.value / max) * 100, 4)}
        <div class="flex flex-1 flex-col items-center gap-2">
            <span class="text-xs font-medium text-foreground">{bar.value}{suffix}</span>
            <div class="flex h-28 w-full items-end rounded-md bg-muted">
                <div
                    class="w-full rounded-md transition-all"
                    style="height: {heightPct}%; background-color: {color};"
                ></div>
            </div>
            <span class="text-xs text-muted-foreground">{bar.label}</span>
        </div>
    {/each}
</div>
