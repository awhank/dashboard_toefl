<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa pengguna yang belum pernah mengganti password (masih sama dengan
 * nomor pegawai/NIP default) untuk ke halaman ganti password lebih dulu.
 *
 * Daftarkan hanya pada route yang BUKAN halaman ganti-password/logout itu
 * sendiri, supaya tidak terjadi redirect loop.
 */
class EnsurePasswordIsChanged
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->password_changed_at === null) {
            return redirect()->route('password.edit');
        }

        return $next($request);
    }
}
