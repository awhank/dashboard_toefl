<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * ~30 pegawai dummy (dosen & tendik) untuk mencoba tampilan dashboard sebelum data asli
     * diimpor dari Excel. Setiap pegawai aktif otomatis mendapat akun login (username & password
     * default = nomor_pegawai), sesuai kebijakan di docs/analisis-kebutuhan.md.
     */
    public function run(): void
    {
        $dosen = Pegawai::factory()
            ->count(20)
            ->state(fn () => [
                'nama' => fake('id_ID')->name(),
                'jenis_pegawai' => 'dosen',
                'jabatan' => fake()->randomElement([
                    'Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Tenaga Pengajar',
                ]),
            ])
            ->create();

        $tendik = Pegawai::factory()
            ->count(10)
            ->state(fn () => [
                'nama' => fake('id_ID')->name(),
                'jenis_pegawai' => 'tendik',
                'jabatan' => fake()->randomElement([
                    'Staf Akademik', 'Staf Kepegawaian', 'Staf Keuangan', 'Pranata Laboratorium',
                ]),
            ])
            ->create();

        $semua = $dosen->concat($tendik);

        // 2 pegawai dinonaktifkan (contoh: pensiun/pindah), untuk mencoba filter status_aktif.
        $semua->take(2)->each(function (Pegawai $pegawai) {
            $pegawai->update([
                'status_aktif' => false,
                'tanggal_nonaktif' => now()->subMonths(fake()->numberBetween(1, 10)),
                'keterangan_nonaktif' => fake()->randomElement(['Pensiun', 'Pindah tugas']),
            ]);
        });

        foreach ($semua as $pegawai) {
            User::factory()->create([
                'pegawai_id' => $pegawai->id,
                'username' => $pegawai->nomor_pegawai,
                'name' => $pegawai->nama,
                'password' => Hash::make($pegawai->nomor_pegawai),
                'role' => 'pegawai',
                'is_active' => $pegawai->status_aktif,
            ]);
        }
    }
}
