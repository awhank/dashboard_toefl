<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    /**
     * Tampilkan form ganti password.
     */
    public function edit(): Response
    {
        /** @var User $user */
        $user = Auth::user();

        return Inertia::render('auth/GantiPassword', [
            'wajibDiganti' => $user->password_changed_at === null,
        ]);
    }

    /**
     * Simpan password baru.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $user->forceFill([
            'password' => Hash::make($request->string('password_baru')),
            'password_changed_at' => now(),
        ])->save();

        return redirect($user->role === 'pegawai' ? '/profil' : '/dashboard')
            ->with('status', 'Password berhasil diganti.');
    }
}
