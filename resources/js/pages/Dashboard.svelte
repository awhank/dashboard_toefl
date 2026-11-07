<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import StatCard from '@/components/StatCard.svelte';
    import DonutChart from '@/components/DonutChart.svelte';
    import BarChart from '@/components/BarChart.svelte';
    import Icon from '@/components/Icon.svelte';
    import { router } from '@inertiajs/svelte';

    interface Props {
        tanggalAcuan: string;
        kpi: {
            pegawaiAktif: number;
            belumTesItp: number;
            capaiTarget: number;
            perluPerhatian: number;
            targetSkorMinimal: number;
            tahunAcuan: number;
        };
        capaiTargetSegments: Array<{ label: string; value: number; color: string }>;
        statusSertifikatSegments: Array<{ label: string; value: number; color: string }>;
        trenPeserta: Array<{ label: string; value: number }>;
        rataRataBagian: Array<{ label: string; value: number }>;
        kandidatPrioritas: Array<{
            nama: string;
            unit: string;
            alasan: string;
            tone: 'primary' | 'muted' | 'ok' | 'warning' | 'danger';
        }>;
    }

    let {
        tanggalAcuan,
        kpi,
        capaiTargetSegments,
        statusSertifikatSegments,
        trenPeserta,
        rataRataBagian,
        kandidatPrioritas,
    }: Props = $props();

    let selectedTanggalAcuan = $state('');

    $effect(() => {
        selectedTanggalAcuan = tanggalAcuan;
    });

    function onTanggalChange() {
        if (selectedTanggalAcuan && selectedTanggalAcuan !== tanggalAcuan) {
            router.get(
                '/dashboard',
                { tanggal_acuan: selectedTanggalAcuan },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }
    }
</script>

<AppHead title="Dashboard">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@800&display=swap"
        rel="stylesheet"
    />
</AppHead>

<AppLayout active="dashboard">
    {#snippet actions()}
        <label class="flex items-center gap-2 text-sm text-muted-foreground">
            Tgl acuan
            <input
                type="date"
                bind:value={selectedTanggalAcuan}
                onchange={onTanggalChange}
                class="rounded-md border border-border px-2 py-1.5 text-sm text-foreground focus:border-brand-teal focus:ring-1 focus:ring-brand-teal focus:outline-none"
            />
        </label>
        <button
            onclick={() => window.print()}
            class="flex items-center gap-2 rounded-md bg-primary px-3 py-1.5 text-sm font-medium text-primary-foreground hover:opacity-90"
        >
            <Icon name="printer" class="h-4 w-4" />
            Cetak
        </button>
    {/snippet}

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- KPI -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard icon="users" label="Pegawai Aktif" value={kpi.pegawaiAktif} hint="Dosen + Tendik" tone="primary" />
            <StatCard
                icon="alert-triangle"
                label="Belum Pernah Tes ITP"
                value={kpi.belumTesItp}
                hint="Termasuk yang hanya pernah Prediction"
                tone="muted"
            />
            <StatCard
                icon="check-circle"
                label="Capai Target Skor"
                value={kpi.capaiTarget}
                hint={`Target tahun ${kpi.tahunAcuan}: ≥ ${kpi.targetSkorMinimal}`}
                tone="ok"
            />
            <StatCard
                icon="clock"
                label="Perlu Perhatian"
                value={kpi.perluPerhatian}
                hint="Sertifikat expired / akan expired"
                tone="warning"
            />
        </div>

        <!-- Charts row -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <Card>
                <h2 class="font-heading mb-4 text-base text-foreground">Capaian Target Skor</h2>
                <DonutChart segments={capaiTargetSegments} centerValue="{kpi.capaiTarget}/{kpi.pegawaiAktif}" centerLabel="capai target" />
            </Card>

            <Card>
                <h2 class="font-heading mb-4 text-base text-foreground">Status Sertifikat ITP</h2>
                <DonutChart segments={statusSertifikatSegments} centerValue={kpi.pegawaiAktif} centerLabel="pegawai aktif" />
            </Card>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <Card>
                <h2 class="font-heading mb-4 text-base text-foreground">Tren Jumlah Peserta per Periode</h2>
                {#if trenPeserta.length > 0}
                    <BarChart bars={trenPeserta} color="var(--color-brand-teal)" />
                {:else}
                    <p class="py-12 text-center text-sm text-muted-foreground">Belum ada data periode tes resmi.</p>
                {/if}
            </Card>

            <Card>
                <h2 class="font-heading mb-4 text-base text-foreground">Rata-rata Skor per Bagian</h2>
                <BarChart bars={rataRataBagian} color="var(--color-brand-green)" suffix=" pts" />
            </Card>
        </div>

        <!-- Kandidat prioritas -->
        <Card>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="font-heading text-base text-foreground">Kandidat Peserta Prioritas</h2>
                <a href="#kandidat" class="text-sm font-medium text-primary hover:underline">Lihat semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs text-muted-foreground uppercase">
                            <th class="py-2 pr-4 font-medium">Nama</th>
                            <th class="py-2 pr-4 font-medium">Jenis</th>
                            <th class="py-2 pr-4 font-medium">Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#if kandidatPrioritas.length === 0}
                            <tr>
                                <td colspan="3" class="py-6 text-center text-sm text-muted-foreground">
                                    Semua pegawai telah mencapai target dan memiliki sertifikat aktif.
                                </td>
                            </tr>
                        {:else}
                            {#each kandidatPrioritas as k (k.nama)}
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2.5 pr-4 font-medium text-foreground">{k.nama}</td>
                                    <td class="py-2.5 pr-4 text-muted-foreground">{k.unit}</td>
                                    <td class="py-2.5 pr-4">
                                        <Badge tone={k.tone}>{k.alasan}</Badge>
                                    </td>
                                </tr>
                            {/each}
                        {/if}
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</AppLayout>
