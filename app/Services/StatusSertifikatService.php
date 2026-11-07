<?php

namespace App\Services;

use App\Enums\StatusSertifikat;
use App\Models\HasilTes;
use App\Models\Pegawai;
use App\Models\TargetSkor;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class StatusSertifikatService
{
    /**
     * Dapatkan target skor minimal untuk tahun dari tanggal acuan.
     */
    public function getTargetSkor(CarbonInterface $tanggalAcuan): int
    {
        $target = TargetSkor::query()
            ->where('tahun', $tanggalAcuan->year)
            ->value('skor_minimal');

        if ($target !== null) {
            return (int) $target;
        }

        // Fallback ke target tahun terakhir yang tercatat, atau default 500
        $latestTarget = TargetSkor::query()
            ->orderByDesc('tahun')
            ->value('skor_minimal');

        return (int) ($latestTarget ?? 500);
    }

    /**
     * Evaluasi status sertifikat untuk satu pegawai.
     *
     * @return array{
     *     status: StatusSertifikat,
     *     capai_target: bool,
     *     skor_terbaru: int|null,
     *     tanggal_tes_terbaru: Carbon|null,
     *     berlaku_sampai: Carbon|null,
     *     alasan_prioritas: string,
     *     prioritas_rank: int,
     *     prioritas_tone: 'primary'|'muted'|'ok'|'warning'|'danger',
     *     latest_itp: HasilTes|null,
     * }
     */
    public function evaluasiPegawai(
        Pegawai $pegawai,
        CarbonInterface $tanggalAcuan,
        int $targetSkor,
        ?HasilTes $latestItp = null,
    ): array {
        $tglAcuan = Carbon::parse($tanggalAcuan)->startOfDay();

        // 1. Belum pernah tes ITP
        if ($latestItp === null) {
            return [
                'status' => StatusSertifikat::BelumTes,
                'capai_target' => false,
                'skor_terbaru' => null,
                'tanggal_tes_terbaru' => null,
                'berlaku_sampai' => null,
                'alasan_prioritas' => 'Belum pernah tes ITP',
                'prioritas_rank' => 1,
                'prioritas_tone' => 'muted',
                'latest_itp' => null,
            ];
        }

        $tanggalTes = Carbon::parse($latestItp->tanggal_tes)->startOfDay();
        $berlakuSampai = $latestItp->berlaku_sampai
            ? Carbon::parse($latestItp->berlaku_sampai)->startOfDay()
            : $tanggalTes->copy()->addMonths(24);

        $tigaBulanLagi = $tglAcuan->copy()->addMonths(3);
        $capaiTarget = $latestItp->skor_total >= $targetSkor;

        // 2. Expired: berlaku_sampai <= tanggal acuan
        if ($berlakuSampai->lessThanOrEqualTo($tglAcuan)) {
            $selisihHari = (int) $berlakuSampai->diffInDays($tglAcuan);
            $alasan = $selisihHari > 0 ? "Expired {$selisihHari} hari lalu" : 'Expired hari ini';

            return [
                'status' => StatusSertifikat::Expired,
                'capai_target' => $capaiTarget,
                'skor_terbaru' => $latestItp->skor_total,
                'tanggal_tes_terbaru' => $tanggalTes,
                'berlaku_sampai' => $berlakuSampai,
                'alasan_prioritas' => $alasan,
                'prioritas_rank' => 2,
                'prioritas_tone' => 'danger',
                'latest_itp' => $latestItp,
            ];
        }

        // 3. Akan expired: tanggal acuan < berlaku_sampai <= tanggal acuan + 3 bulan
        if ($berlakuSampai->lessThanOrEqualTo($tigaBulanLagi)) {
            $selisihHari = (int) $tglAcuan->diffInDays($berlakuSampai);
            $alasan = "Akan expired {$selisihHari} hari lagi";

            return [
                'status' => StatusSertifikat::AkanExpired,
                'capai_target' => $capaiTarget,
                'skor_terbaru' => $latestItp->skor_total,
                'tanggal_tes_terbaru' => $tanggalTes,
                'berlaku_sampai' => $berlakuSampai,
                'alasan_prioritas' => $alasan,
                'prioritas_rank' => 3,
                'prioritas_tone' => 'warning',
                'latest_itp' => $latestItp,
            ];
        }

        // 4. Berlaku: berlaku_sampai > tanggal acuan + 3 bulan
        if (! $capaiTarget) {
            return [
                'status' => StatusSertifikat::Berlaku,
                'capai_target' => false,
                'skor_terbaru' => $latestItp->skor_total,
                'tanggal_tes_terbaru' => $tanggalTes,
                'berlaku_sampai' => $berlakuSampai,
                'alasan_prioritas' => "Skor {$latestItp->skor_total}, belum capai target (≥ {$targetSkor})",
                'prioritas_rank' => 4,
                'prioritas_tone' => 'warning',
                'latest_itp' => $latestItp,
            ];
        }

        return [
            'status' => StatusSertifikat::Berlaku,
            'capai_target' => true,
            'skor_terbaru' => $latestItp->skor_total,
            'tanggal_tes_terbaru' => $tanggalTes,
            'berlaku_sampai' => $berlakuSampai,
            'alasan_prioritas' => "Capai target (skor {$latestItp->skor_total})",
            'prioritas_rank' => 5,
            'prioritas_tone' => 'ok',
            'latest_itp' => $latestItp,
        ];
    }

    /**
     * Evaluasi seluruh pegawai aktif beserta status sertifikat dan prioritasnya.
     *
     * @return Collection<int, array{
     *     pegawai: Pegawai,
     *     status: StatusSertifikat,
     *     capai_target: bool,
     *     skor_terbaru: int|null,
     *     tanggal_tes_terbaru: Carbon|null,
     *     berlaku_sampai: Carbon|null,
     *     alasan_prioritas: string,
     *     prioritas_rank: int,
     *     prioritas_tone: 'primary'|'muted'|'ok'|'warning'|'danger',
     *     latest_itp: HasilTes|null,
     * }>
     */
    public function evaluasiSemuaPegawaiAktif(CarbonInterface $tanggalAcuan): Collection
    {
        $targetSkor = $this->getTargetSkor($tanggalAcuan);

        $pegawaiList = Pegawai::query()
            ->where('status_aktif', true)
            ->with([
                'hasilTes' => function ($query) {
                    $query->whereHas('periodeTes.jenisTes', function ($q) {
                        $q->where('is_resmi', true);
                    })
                    ->orderByDesc('tanggal_tes')
                    ->orderByDesc('id');
                },
            ])
            ->orderBy('nama')
            ->get();

        return $pegawaiList->map(function (Pegawai $pegawai) use ($tanggalAcuan, $targetSkor) {
            /** @var HasilTes|null $latestItp */
            $latestItp = $pegawai->hasilTes->first();

            $evaluasi = $this->evaluasiPegawai($pegawai, $tanggalAcuan, $targetSkor, $latestItp);

            return array_merge(['pegawai' => $pegawai], $evaluasi);
        });
    }

    /**
     * Dapatkan daftar kandidat prioritas peserta tes berikutnya.
     * Diurutkan dari yang paling membutuhkan tes (prioritas 1 sampai 4).
     *
     * @return Collection<int, array{
     *     pegawai: Pegawai,
     *     nama: string,
     *     unit: string,
     *     alasan: string,
     *     tone: 'primary'|'muted'|'ok'|'warning'|'danger',
     *     prioritas_rank: int,
     *     skor_terbaru: int|null,
     *     berlaku_sampai: Carbon|null,
     * }>
     */
    public function getKandidatPrioritas(CarbonInterface $tanggalAcuan, ?int $limit = null): Collection
    {
        $evaluasi = $this->evaluasiSemuaPegawaiAktif($tanggalAcuan);

        // Filter hanya yang membutuhkan tes (prioritas 1 sampai 4)
        $kandidat = $evaluasi
            ->filter(fn (array $item) => $item['prioritas_rank'] <= 4)
            ->sort(function (array $a, array $b) {
                // Urutan utama: rank prioritas (1: belum tes -> 2: expired -> 3: akan expired -> 4: belum capai target)
                if ($a['prioritas_rank'] !== $b['prioritas_rank']) {
                    return $a['prioritas_rank'] <=> $b['prioritas_rank'];
                }

                // Urutan sekunder:
                // Jika expired atau akan expired, yang berlaku_sampai lebih awal didahulukan
                if (in_array($a['prioritas_rank'], [2, 3], true) && $a['berlaku_sampai'] && $b['berlaku_sampai']) {
                    $cmp = $a['berlaku_sampai']->timestamp <=> $b['berlaku_sampai']->timestamp;
                    if ($cmp !== 0) {
                        return $cmp;
                    }
                }

                // Jika belum capai target, skor terendah didahulukan
                if ($a['prioritas_rank'] === 4 && $a['skor_terbaru'] !== $b['skor_terbaru']) {
                    return ($a['skor_terbaru'] ?? 0) <=> ($b['skor_terbaru'] ?? 0);
                }

                return strcmp($a['pegawai']->nama, $b['pegawai']->nama);
            })
            ->values()
            ->map(function (array $item) {
                /** @var Pegawai $pegawai */
                $pegawai = $item['pegawai'];

                return [
                    'pegawai' => $pegawai,
                    'nama' => $pegawai->nama,
                    'unit' => ucfirst($pegawai->jenis_pegawai),
                    'alasan' => $item['alasan_prioritas'],
                    'tone' => $item['prioritas_tone'],
                    'prioritas_rank' => $item['prioritas_rank'],
                    'skor_terbaru' => $item['skor_terbaru'],
                    'berlaku_sampai' => $item['berlaku_sampai'],
                ];
            });

        if ($limit !== null && $limit > 0) {
            return $kandidat->take($limit);
        }

        return $kandidat;
    }
}

