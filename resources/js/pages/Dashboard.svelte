<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import Card from '@/components/ui/Card.svelte';
    import Badge from '@/components/ui/Badge.svelte';
    import StatCard from '@/components/StatCard.svelte';
    import DonutChart from '@/components/DonutChart.svelte';
    import BarChart from '@/components/BarChart.svelte';
    import Icon from '@/components/Icon.svelte';

    // Data contoh untuk pratinjau desain — akan diganti data asli dari controller.
    const kpi = {
        pegawaiAktif: 204,
        belumTesItp: 87,
        capaiTarget: 64,
        perluPerhatian: 17, // expired + akan expired
    };

    const capaiTargetSegments = [
        { label: 'Capai target (≥ 500)', value: 64, color: 'var(--color-brand-green)' },
        { label: 'Belum capai target', value: 53, color: 'var(--color-muted-foreground)' },
    ];

    const statusSertifikatSegments = [
        { label: 'Berlaku', value: 89, color: 'var(--color-status-ok)' },
        { label: 'Akan expired (≤ 3 bln)', value: 12, color: 'var(--color-status-warning)' },
        { label: 'Expired', value: 16, color: 'var(--color-status-danger)' },
        { label: 'Belum pernah tes ITP', value: 87, color: 'var(--color-status-muted)' },
    ];

    const trenPeserta = [
        { label: '2023 G1', value: 42 },
        { label: '2023 G2', value: 38 },
        { label: '2024 G1', value: 51 },
        { label: '2024 G2', value: 45 },
        { label: '2025 G1', value: 60 },
    ];

    const rataRataBagian = [
        { label: 'Listening', value: 52 },
        { label: 'Structure', value: 49 },
        { label: 'Reading', value: 50 },
    ];

    const kandidatPrioritas = [
        { nama: 'Muhammad Fadli', unit: 'Dosen', alasan: 'Belum pernah tes', tone: 'muted' as const },
        { nama: 'Siti Rahmawati', unit: 'Tendik', alasan: 'Expired 14 hari lalu', tone: 'danger' as const },
        { nama: 'Ahmad Zaini', unit: 'Dosen', alasan: 'Akan expired 20 hari lagi', tone: 'warning' as const },
        { nama: 'Norhidayah', unit: 'Dosen', alasan: 'Skor 470, belum capai target', tone: 'warning' as const },
    ];

    let tanggalAcuan = $state(new Date().toISOString().slice(0, 10));
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
                bind:value={tanggalAcuan}
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
                hint="Target tahun 2026: ≥ 500"
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
                <BarChart bars={trenPeserta} color="var(--color-brand-teal)" />
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
                        {#each kandidatPrioritas as k (k.nama)}
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2.5 pr-4 font-medium text-foreground">{k.nama}</td>
                                <td class="py-2.5 pr-4 text-muted-foreground">{k.unit}</td>
                                <td class="py-2.5 pr-4">
                                    <Badge tone={k.tone}>{k.alasan}</Badge>
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</AppLayout>
