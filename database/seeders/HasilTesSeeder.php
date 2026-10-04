<?php

namespace Database\Seeders;

use App\Models\HasilTes;
use App\Models\ImportBatch;
use App\Models\JenisTes;
use App\Models\Pegawai;
use App\Models\PeriodeTes;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HasilTesSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Membuat beberapa periode tes ITP (2023–2025) dan hasil tes untuk sebagian pegawai saja —
     * sebagian pegawai sengaja tidak diikutkan supaya kategori "belum pernah tes" tetap terlihat
     * di dashboard. Skor & tanggal disebar agar status "berlaku / akan expired / expired" semua
     * terwakili. Juga satu periode Prediction untuk menunjukkan aturan "Prediction saja = belum
     * pernah tes ITP".
     */
    public function run(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $itp = JenisTes::query()->where('kode', 'ITP')->firstOrFail();
        $pred = JenisTes::query()->where('kode', 'PRED')->firstOrFail();

        $aktif = Pegawai::query()->where('status_aktif', true)->get()->shuffle();

        // ~1/3 pegawai aktif sengaja tidak pernah diikutkan tes ITP sama sekali.
        $neverTested = $aktif->take((int) ceil($aktif->count() / 3));
        $pool = $aktif->diff($neverTested)->values();

        $periodeItp = collect([
            ['tahun' => 2023, 'gelombang' => 1, 'tanggal' => '2023-03-15'],
            ['tahun' => 2023, 'gelombang' => 2, 'tanggal' => '2023-09-20'],
            ['tahun' => 2024, 'gelombang' => 1, 'tanggal' => '2024-03-18'],
            ['tahun' => 2024, 'gelombang' => 2, 'tanggal' => '2024-09-16'],
            ['tahun' => 2025, 'gelombang' => 1, 'tanggal' => '2025-03-14'],
            ['tahun' => 2025, 'gelombang' => 2, 'tanggal' => '2025-09-19'],
        ])->map(fn (array $data) => PeriodeTes::query()->updateOrCreate(
            ['jenis_tes_id' => $itp->id, 'tahun' => $data['tahun'], 'gelombang' => $data['gelombang']],
            [
                'nama' => "TOEFL ITP {$data['tahun']} Gelombang {$data['gelombang']}",
                'tanggal_pelaksanaan' => $data['tanggal'],
                'penyelenggara' => 'Pusat Bahasa Poltekkes Banjarmasin',
                'created_by' => $admin->id,
            ],
        ));

        // Satu batch impor contoh, dipakai untuk sebagian hasil pada periode pertama.
        $batchPertama = ImportBatch::query()->create([
            'periode_tes_id' => $periodeItp->first()->id,
            'user_id' => $admin->id,
            'nama_file' => 'hasil_toefl_itp_2023_g1.xlsx',
            'jumlah_baris' => 0,
            'jumlah_sukses' => 0,
            'jumlah_gagal' => 0,
            'status' => 'selesai',
        ]);

        $sukses = 0;

        foreach ($periodeItp as $i => $periode) {
            $peserta = $pool->random(min($pool->count(), fake()->numberBetween(5, 9)));
            $batchId = $i === 0 ? $batchPertama->id : null;

            foreach ($peserta as $pegawai) {
                $listening = fake()->numberBetween(31, 68);
                $structure = fake()->numberBetween(31, 68);
                $reading = fake()->numberBetween(31, 68);
                $tanggalTes = Carbon::parse($periode->tanggal_pelaksanaan);

                $dibuat = HasilTes::query()->create([
                    'periode_tes_id' => $periode->id,
                    'pegawai_id' => $pegawai->id,
                    'skor_listening' => $listening,
                    'skor_structure' => $structure,
                    'skor_reading' => $reading,
                    'skor_total' => (int) round(($listening + $structure + $reading) * 10 / 3),
                    'tanggal_tes' => $tanggalTes,
                    'berlaku_sampai' => $tanggalTes->copy()->addMonths(24),
                    'no_sertifikat' => 'ITP-'.$periode->tahun.'-'.fake()->unique()->numerify('#####'),
                    'sumber_input' => $batchId ? 'impor' : 'manual',
                    'import_batch_id' => $batchId,
                    'created_by' => $admin->id,
                ]);

                $sukses += (int) ($dibuat->wasRecentlyCreated);
            }
        }

        $batchPertama->update([
            'jumlah_baris' => $sukses,
            'jumlah_sukses' => $sukses,
        ]);

        // Periode Prediction: 2 pegawai yang belum pernah ITP pernah ikut Prediction saja —
        // tetap harus terhitung "belum pernah tes ITP" oleh StatusSertifikatService nanti.
        $periodePred = PeriodeTes::query()->updateOrCreate(
            ['jenis_tes_id' => $pred->id, 'tahun' => 2025, 'gelombang' => 1],
            [
                'nama' => 'TOEFL Prediction 2025 Gelombang 1',
                'tanggal_pelaksanaan' => '2025-06-10',
                'penyelenggara' => 'Pusat Bahasa Poltekkes Banjarmasin',
                'created_by' => $admin->id,
            ],
        );

        foreach ($neverTested->take(2) as $pegawai) {
            $listening = fake()->numberBetween(31, 68);
            $structure = fake()->numberBetween(31, 68);
            $reading = fake()->numberBetween(31, 68);

            HasilTes::query()->create([
                'periode_tes_id' => $periodePred->id,
                'pegawai_id' => $pegawai->id,
                'skor_listening' => $listening,
                'skor_structure' => $structure,
                'skor_reading' => $reading,
                'skor_total' => (int) round(($listening + $structure + $reading) * 10 / 3),
                'tanggal_tes' => $periodePred->tanggal_pelaksanaan,
                'berlaku_sampai' => null,
                'sumber_input' => 'manual',
                'created_by' => $admin->id,
            ]);
        }
    }
}
