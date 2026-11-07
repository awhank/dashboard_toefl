<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pegawai\NonaktifkanPegawaiRequest;
use App\Http\Requests\Pegawai\StorePegawaiRequest;
use App\Http\Requests\Pegawai\UpdatePegawaiRequest;
use App\Models\Pegawai;
use App\Models\User;
use App\Services\ImportPegawaiService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PegawaiController extends Controller
{
    /**
     * Tampilkan daftar master pegawai.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->toString();
        $jenis = $request->string('jenis')->trim()->toString();
        $status = $request->string('status')->trim()->toString();

        $query = Pegawai::query()
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('nomor_pegawai', 'like', "%{$search}%")
                        ->orWhere('jabatan', 'like', "%{$search}%");
                });
            })
            ->when(in_array($jenis, ['dosen', 'tendik'], true), function (Builder $query) use ($jenis) {
                $query->where('jenis_pegawai', $jenis);
            })
            ->when($status !== '', function (Builder $query) use ($status) {
                if ($status === 'aktif') {
                    $query->where('status_aktif', true);
                } elseif ($status === 'nonaktif') {
                    $query->where('status_aktif', false);
                }
            })
            ->orderBy('nama');

        $pegawaiList = $query->paginate(15)->withQueryString();

        return Inertia::render('Pegawai/Index', [
            'pegawai' => $pegawaiList,
            'filters' => [
                'search' => $search ?: null,
                'jenis' => $jenis ?: null,
                'status' => $status ?: null,
            ],
        ]);
    }

    /**
     * Simpan data pegawai baru beserta pembuatan akun login otomatis.
     */
    public function store(StorePegawaiRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $pegawai = Pegawai::query()->create([
                'nomor_pegawai' => $request->string('nomor_pegawai')->trim(),
                'jenis_nomor' => $request->string('jenis_nomor'),
                'nama' => $request->string('nama')->trim(),
                'jenis_pegawai' => $request->string('jenis_pegawai'),
                'jenis_kelamin' => $request->filled('jenis_kelamin') ? $request->string('jenis_kelamin') : null,
                'tanggal_lahir' => $request->filled('tanggal_lahir') ? $request->date('tanggal_lahir') : null,
                'jabatan' => $request->filled('jabatan') ? $request->string('jabatan')->trim() : null,
                'status_aktif' => true,
            ]);

            User::query()->create([
                'pegawai_id' => $pegawai->id,
                'username' => $pegawai->nomor_pegawai,
                'name' => $pegawai->nama,
                'password' => Hash::make($pegawai->nomor_pegawai),
                'role' => 'pegawai',
                'is_active' => true,
                'password_changed_at' => null,
            ]);
        });

        return redirect()->back()->with('success', 'Data pegawai berhasil ditambahkan dan akun login telah dibuat.');
    }

    /**
     * Perbarui data profil pegawai.
     */
    public function update(UpdatePegawaiRequest $request, Pegawai $pegawai): RedirectResponse
    {
        DB::transaction(function () use ($request, $pegawai) {
            $nomorLama = $pegawai->nomor_pegawai;

            $pegawai->update([
                'nomor_pegawai' => $request->string('nomor_pegawai')->trim(),
                'jenis_nomor' => $request->string('jenis_nomor'),
                'nama' => $request->string('nama')->trim(),
                'jenis_pegawai' => $request->string('jenis_pegawai'),
                'jenis_kelamin' => $request->filled('jenis_kelamin') ? $request->string('jenis_kelamin') : null,
                'tanggal_lahir' => $request->filled('tanggal_lahir') ? $request->date('tanggal_lahir') : null,
                'jabatan' => $request->filled('jabatan') ? $request->string('jabatan')->trim() : null,
            ]);

            // Sinkronkan akun login jika nomor pegawai atau nama diperbarui
            if ($pegawai->user) {
                $pegawai->user->update([
                    'username' => $pegawai->nomor_pegawai,
                    'name' => $pegawai->nama,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Nonaktifkan pegawai dan blokir akses akun login-nya.
     */
    public function nonaktifkan(NonaktifkanPegawaiRequest $request, Pegawai $pegawai): RedirectResponse
    {
        DB::transaction(function () use ($request, $pegawai) {
            $pegawai->update([
                'status_aktif' => false,
                'tanggal_nonaktif' => $request->date('tanggal_nonaktif'),
                'keterangan_nonaktif' => $request->string('keterangan_nonaktif')->trim(),
            ]);

            $pegawai->user?->update([
                'is_active' => false,
            ]);
        });

        return redirect()->back()->with('success', "Pegawai {$pegawai->nama} telah dinonaktifkan.");
    }

    /**
     * Aktifkan kembali pegawai yang sebelumnya dinonaktifkan.
     */
    public function aktifkan(Pegawai $pegawai): RedirectResponse
    {
        DB::transaction(function () use ($pegawai) {
            $pegawai->update([
                'status_aktif' => true,
                'tanggal_nonaktif' => null,
                'keterangan_nonaktif' => null,
            ]);

            $pegawai->user?->update([
                'is_active' => true,
            ]);
        });

        return redirect()->back()->with('success', "Pegawai {$pegawai->nama} berhasil diaktifkan kembali.");
    }

    /**
     * Reset password akun pegawai kembali ke nomor pegawainya.
     */
    public function resetPassword(Pegawai $pegawai): RedirectResponse
    {
        if (! $pegawai->user) {
            return redirect()->back()->with('error', 'Pegawai ini belum memiliki akun pengguna.');
        }

        $pegawai->user->update([
            'password' => Hash::make($pegawai->nomor_pegawai),
            'password_changed_at' => null,
        ]);

        return redirect()->back()->with('success', "Password akun pegawai {$pegawai->nama} berhasil direset ke nomor pegawai ({$pegawai->nomor_pegawai}).");
    }

    /**
     * Unduh template berkas Excel standar untuk impor pegawai.
     */
    public function downloadTemplate(ImportPegawaiService $importService): StreamedResponse
    {
        return $importService->generateTemplateResponse();
    }

    /**
     * Proses impor data pegawai massal dari berkas Excel.
     */
    public function import(Request $request, ImportPegawaiService $importService): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'update_existing' => ['nullable', 'boolean'],
        ], [
            'file.required' => 'Berkas Excel wajib diunggah.',
            'file.file' => 'Unggahan harus berupa berkas valid.',
            'file.mimes' => 'Format berkas harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran berkas maksimal adalah 10 MB.',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');
        $updateExisting = $request->boolean('update_existing', true);

        // return "Halo";

        $result = $importService->import($file, $updateExisting);

        $pesan = "Proses impor selesai: {$result['created']} data pegawai baru ditambahkan, {$result['updated']} data diperbarui, dan {$result['skipped']} data dilewati.";

        if (! empty($result['errors'])) {
            return redirect()->back()
                ->with('success', $pesan)
                ->with('error', 'Beberapa baris data gagal diproses: ' . implode('; ', array_slice($result['errors'], 0, 3)));
        }

        return redirect()->back()->with('success', $pesan);
    }
}

