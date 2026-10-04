<script lang="ts">
    type Segment = { label: string; value: number; color: string };

    let {
        segments,
        centerLabel = '',
        centerValue = '',
    }: {
        segments: Segment[];
        centerLabel?: string;
        centerValue?: string | number;
    } = $props();

    const size = 160;
    const stroke = 22;
    const radius = (size - stroke) / 2;
    const circumference = 2 * Math.PI * radius;

    const total = $derived(segments.reduce((sum, s) => sum + s.value, 0) || 1);

    let cursor = 0;
    const arcs = $derived(
        (() => {
            cursor = 0;
            return segments.map((s) => {
                const fraction = s.value / total;
                const dash = fraction * circumference;
                const arc = {
                    ...s,
                    dashArray: `${dash} ${circumference - dash}`,
                    dashOffset: -cursor,
                };
                cursor += dash;
                return arc;
            });
        })(),
    );
</script>

<div class="flex items-center gap-6">
    <svg width={size} height={size} viewBox="0 0 {size} {size}" class="-rotate-90">
        <circle cx={size / 2} cy={size / 2} r={radius} fill="none" stroke="var(--color-muted)" stroke-width={stroke} />
        {#each arcs as arc (arc.label)}
            <circle
                cx={size / 2}
                cy={size / 2}
                r={radius}
                fill="none"
                stroke={arc.color}
                stroke-width={stroke}
                stroke-dasharray={arc.dashArray}
                stroke-dashoffset={arc.dashOffset}
                stroke-linecap="butt"
            />
        {/each}
    </svg>
    <div>
        {#if centerLabel || centerValue}
            <p class="font-heading text-2xl text-foreground">{centerValue}</p>
            <p class="mb-3 text-xs text-muted-foreground">{centerLabel}</p>
        {/if}
        <ul class="space-y-1.5">
            {#each segments as s (s.label)}
                <li class="flex items-center gap-2 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {s.color}"></span>
                    <span class="text-muted-foreground">{s.label}</span>
                    <span class="font-medium text-foreground">{s.value}</span>
                </li>
            {/each}
        </ul>
    </div>
</div>
