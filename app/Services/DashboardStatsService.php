<?php

namespace App\Services;

use App\Enums\StatusSertifikat;
use App\Models\PeriodeTes;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class DashboardStatsService
{
    public function __construct(
        protected StatusSertifikatService $statusService,
    ) {}

    /**
     * Dapatkan seluruh data statistik untuk tampilan Dashboard.
     *
     * @return array{
     *     tanggalAcuan: string,
     *     kpi: array{
     *         pegawaiAktif: int,
     *         belumTesItp: int,
     *         capaiTarget: int,
     *         perluPerhatian: int,
     *         targetSkorMinimal: int,
     *         tahunAcuan: int,
     *     },
     *     capaiTargetSegments: list<array{label: string, value: int, color: string}>,
     *     statusSertifikatSegments: list<array{label: string, value: int, color: string}>,
     *     trenPeserta: list<array{label: string, value: int}>,
     *     rataRataBagian: list<array{label: string, value: int}>,
     *     kandidatPrioritas: list<array{nama: string, unit: string, alasan: string, tone: 'primary'|'muted'|'ok'|'warning'|'danger'}>,
     * }
     */
    public function getDashboardData(?string $tanggalAcuanString = null): array
    {
        $tanggalAcuan = $tanggalAcuanString
            ? Carbon::parse($tanggalAcuanString)->startOfDay()
            : Carbon::today();

        $targetSkorMinimal = $this->statusService->getTargetSkor($tanggalAcuan);
        $evaluasiSemua = $this->statusService->evaluasiSemuaPegawaiAktif($tanggalAcuan);

        $pegawaiAktifCount = $evaluasiSemua->count();

        // 1. Hitung agregasi status sertifikat
        $belumTesCount = $evaluasiSemua->where('status', StatusSertifikat::BelumTes)->count();
        $berlakuCount = $evaluasiSemua->where('status', StatusSertifikat::Berlaku)->count();
        $akanExpiredCount = $evaluasiSemua->where('status', StatusSertifikat::AkanExpired)->count();
        $expiredCount = $evaluasiSemua->where('status', StatusSertifikat::Expired)->count();

        $perluPerhatianCount = $akanExpiredCount + $expiredCount;

        // 2. Hitung capaian target skor
        $capaiTargetCount = $evaluasiSemua->where('capai_target', true)->count();
        $belumCapaiTargetCount = $pegawaiAktifCount - $capaiTargetCount;

        // 3. Tren peserta per periode tes (6 periode ITP terakhir)
        $periodeList = PeriodeTes::query()
            ->whereHas('jenisTes', fn ($q) => $q->where('is_resmi', true))
            ->withCount('hasilTes')
            ->orderBy('tahun')
            ->orderBy('gelombang')
            ->get();

        $trenPeserta = $periodeList->map(fn (PeriodeTes $p) => [
            'label' => "{$p->tahun} G{$p->gelombang}",
            'value' => (int) $p->hasil_tes_count,
        ])->values()->all();

        // 4. Rata-rata skor per bagian dari tes ITP terbaru pegawai
        $pesertaDenganTes = $evaluasiSemua->filter(fn ($item) => $item['latest_itp'] !== null);
        $jumlahPesertaTes = $pesertaDenganTes->count();

        $avgListening = $jumlahPesertaTes > 0
            ? (int) round($pesertaDenganTes->avg(fn ($item) => $item['latest_itp']->skor_listening))
            : 0;

        $avgStructure = $jumlahPesertaTes > 0
            ? (int) round($pesertaDenganTes->avg(fn ($item) => $item['latest_itp']->skor_structure))
            : 0;

        $avgReading = $jumlahPesertaTes > 0
            ? (int) round($pesertaDenganTes->avg(fn ($item) => $item['latest_itp']->skor_reading))
            : 0;

        $rataRataBagian = [
            ['label' => 'Listening', 'value' => $avgListening],
            ['label' => 'Structure', 'value' => $avgStructure],
            ['label' => 'Reading', 'value' => $avgReading],
        ];

        // 5. Kandidat prioritas (5 teratas untuk preview ringkas di dashboard)
        $kandidatPrioritas = $this->statusService->getKandidatPrioritas($tanggalAcuan, 5)
            ->map(fn (array $k) => [
                'nama' => $k['nama'],
                'unit' => $k['unit'],
                'alasan' => $k['alasan'],
                'tone' => $k['tone'],
            ])
            ->all();

        return [
            'tanggalAcuan' => $tanggalAcuan->toDateString(),
            'kpi' => [
                'pegawaiAktif' => $pegawaiAktifCount,
                'belumTesItp' => $belumTesCount,
                'capaiTarget' => $capaiTargetCount,
                'perluPerhatian' => $perluPerhatianCount,
                'targetSkorMinimal' => $targetSkorMinimal,
                'tahunAcuan' => $tanggalAcuan->year,
            ],
            'capaiTargetSegments' => [
                [
                    'label' => "Capai target (≥ {$targetSkorMinimal})",
                    'value' => $capaiTargetCount,
                    'color' => 'var(--color-brand-green)',
                ],
                [
                    'label' => 'Belum capai target',
                    'value' => $belumCapaiTargetCount,
                    'color' => 'var(--color-muted-foreground)',
                ],
            ],
            'statusSertifikatSegments' => [
                [
                    'label' => 'Berlaku',
                    'value' => $berlakuCount,
                    'color' => 'var(--color-status-ok)',
                ],
                [
                    'label' => 'Akan expired (≤ 3 bln)',
                    'value' => $akanExpiredCount,
                    'color' => 'var(--color-status-warning)',
                ],
                [
                    'label' => 'Expired',
                    'value' => $expiredCount,
                    'color' => 'var(--color-status-danger)',
                ],
                [
                    'label' => 'Belum pernah tes ITP',
                    'value' => $belumTesCount,
                    'color' => 'var(--color-status-muted)',
                ],
            ],
            'trenPeserta' => $trenPeserta,
            'rataRataBagian' => $rataRataBagian,
            'kandidatPrioritas' => $kandidatPrioritas,
        ];
    }
}

