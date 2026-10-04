<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

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
            // Pratinjau desain — akan digantikan controller & data asli pada tahap implementasi.
            Route::inertia('/dashboard', 'Dashboard')->name('dashboard');
        });
    });
});
