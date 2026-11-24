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
