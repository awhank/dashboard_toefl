<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Login');
    }

    /**
     * Proses autentikasi.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended($this->redirectPathFor($user));
    }

    /**
     * Logout.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Halaman tujuan setelah login, tergantung peran.
     */
    private function redirectPathFor(User $user): string
    {
        return match ($user->role) {
            'pegawai' => '/profil',
            default => '/dashboard',
        };
    }
}
