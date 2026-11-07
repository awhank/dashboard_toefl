<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
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

            Route::middleware('role:admin')->group(function () {
                Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
                Route::post('/pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
                Route::put('/pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('pegawai.update');
                Route::post('/pegawai/{pegawai}/nonaktifkan', [PegawaiController::class, 'nonaktifkan'])->name('pegawai.nonaktifkan');
                Route::post('/pegawai/{pegawai}/aktifkan', [PegawaiController::class, 'aktifkan'])->name('pegawai.aktifkan');
                Route::post('/pegawai/{pegawai}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.reset-password');
            });
        });
    });
});
