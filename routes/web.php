<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisTesController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\PeriodeTesController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    /** @var User $user */
    $user = Auth::user();

    return redirect($user->role === 'pegawai' ? '/profil' : '/dashboard');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/ganti-password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/ganti-password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('password.changed')->group(function () {
        Route::get('/profil', fn () => Inertia::render('Profil'))->name('profil');

        Route::middleware('role:admin,pimpinan')->group(function () {
            Route::get('/dashboard', DashboardController::class)->name('dashboard');
            Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
            Route::get('/pegawai/template-import', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template-import');
            Route::get('/periode-tes', [PeriodeTesController::class, 'index'])->name('periode-tes.index');

            // shortcut: data contoh untuk preview halaman; ganti dengan HasilTesController@index saat backend siap.
            Route::get('/hasil-tes', function () {
                $itp = [
                    'id' => 1,
                    'kode' => 'ITP',
                    'nama' => 'TOEFL ITP',
                    'is_resmi' => true,
                    'skor_bagian_min' => 31,
                    'skor_bagian_max' => 68,
                    'skor_total_min' => 310,
                    'skor_total_max' => 677,
                    'masa_berlaku_bulan' => 24,
                ];

                $pred = [
                    'id' => 2,
                    'kode' => 'PRED',
                    'nama' => 'TOEFL Prediction',
                    'is_resmi' => false,
                    'skor_bagian_min' => 31,
                    'skor_bagian_max' => 68,
                    'skor_total_min' => 310,
                    'skor_total_max' => 677,
                    'masa_berlaku_bulan' => null,
                ];

                $periodeItp = [
                    'id' => 1,
                    'nama' => 'TOEFL ITP 2026 Gelombang 1',
                    'tahun' => 2026,
                    'gelombang' => 1,
                    'tanggal_pelaksanaan' => '2026-03-14',
                    'jenis_tes' => $itp,
                ];

                $periodePred = [
                    'id' => 2,
                    'nama' => 'TOEFL Prediction 2025 Gelombang 1',
                    'tahun' => 2025,
                    'gelombang' => 1,
                    'tanggal_pelaksanaan' => '2025-06-10',
                    'jenis_tes' => $pred,
                ];

                $pegawai = [
                    ['id' => 1, 'nomor_pegawai' => '198503152010012001', 'nama' => 'Dr. Siti Aminah, S.Kep., Ns., M.Kep.', 'status_aktif' => true],
                    ['id' => 2, 'nomor_pegawai' => '199007212015031002', 'nama' => 'Ahmad Fauzi, S.Kom.', 'status_aktif' => true],
                    ['id' => 3, 'nomor_pegawai' => '198811102012042003', 'nama' => 'Rina Marlina, S.ST.', 'status_aktif' => false],
                ];

                $hasilTes = [
                    [
                        'id' => 1,
                        'periode_tes_id' => $periodeItp['id'],
                        'pegawai_id' => $pegawai[0]['id'],
                        'skor_listening' => 65,
                        'skor_structure' => 62,
                        'skor_reading' => 60,
                        'skor_total' => 623,
                        'tanggal_tes' => '2026-03-14',
                        'berlaku_sampai' => '2028-03-14',
                        'no_sertifikat' => 'ITP-2026-00001',
                        'sumber_input' => 'impor',
                        'periode_tes' => $periodeItp,
                        'pegawai' => $pegawai[0],
                    ],
                    [
                        'id' => 2,
                        'periode_tes_id' => $periodeItp['id'],
                        'pegawai_id' => $pegawai[1]['id'],
                        'skor_listening' => 55,
                        'skor_structure' => 58,
                        'skor_reading' => 52,
                        'skor_total' => 550,
                        'tanggal_tes' => '2026-03-14',
                        'berlaku_sampai' => '2028-03-14',
                        'no_sertifikat' => 'ITP-2026-00002',
                        'sumber_input' => 'manual',
                        'periode_tes' => $periodeItp,
                        'pegawai' => $pegawai[1],
                    ],
                    [
                        'id' => 3,
                        'periode_tes_id' => $periodePred['id'],
                        'pegawai_id' => $pegawai[2]['id'],
                        'skor_listening' => 60,
                        'skor_structure' => 57,
                        'skor_reading' => 55,
                        'skor_total' => 573,
                        'tanggal_tes' => '2025-06-10',
                        'berlaku_sampai' => null,
                        'no_sertifikat' => null,
                        'sumber_input' => 'manual',
                        'periode_tes' => $periodePred,
                        'pegawai' => $pegawai[2],
                    ],
                ];

                return Inertia::render('HasilTes/Index', [
                    'hasilTes' => [
                        'data' => $hasilTes,
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 15,
                        'total' => count($hasilTes),
                        'from' => 1,
                        'to' => count($hasilTes),
                        'links' => [
                            ['url' => null, 'label' => '&laquo; Previous', 'active' => false],
                            ['url' => '/hasil-tes?page=1', 'label' => '1', 'active' => true],
                            ['url' => null, 'label' => 'Next &raquo;', 'active' => false],
                        ],
                    ],
                    'periodeTes' => [$periodeItp, $periodePred],
                    'pegawai' => $pegawai,
                    'filters' => [
                        'search' => null,
                        'periode_tes_id' => null,
                        'sumber_input' => null,
                    ],
                ]);
            })->name('hasil-tes.index');

            Route::middleware('role:admin')->group(function () {
                Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
                Route::post('/pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
                Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('pegawai.update');
                Route::post('/pegawai/{pegawai}/nonaktifkan', [PegawaiController::class, 'nonaktifkan'])->name('pegawai.nonaktifkan');
                Route::post('/pegawai/{pegawai}/aktifkan', [PegawaiController::class, 'aktifkan'])->name('pegawai.aktifkan');
                Route::post('/pegawai/{pegawai}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.reset-password');
                Route::get('/jenis-tes', [JenisTesController::class, 'index'])->name('jenis-tes.index');
                Route::post('/jenis-tes', [JenisTesController::class, 'store'])->name('jenis-tes.store');
                Route::put('/jenis-tes/{jenisTes}', [JenisTesController::class, 'update'])->name('jenis-tes.update');
                Route::delete('/jenis-tes/{jenisTes}', [JenisTesController::class, 'destroy'])->name('jenis-tes.destroy');
                Route::post('/periode-tes', [PeriodeTesController::class, 'store'])->name('periode-tes.store');
                Route::put('/periode-tes/{periodeTes}', [PeriodeTesController::class, 'update'])->name('periode-tes.update');
                Route::delete('/periode-tes/{periodeTes}', [PeriodeTesController::class, 'destroy'])->name('periode-tes.destroy');
            });
        });
    });
});
