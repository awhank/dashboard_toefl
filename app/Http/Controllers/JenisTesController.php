<?php

namespace App\Http\Controllers;

use App\Http\Requests\JenisTes\StoreJenisTesRequest;
use App\Http\Requests\JenisTes\UpdateJenisTesRequest;
use App\Models\JenisTes;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class JenisTesController extends Controller
{
    /**
     * Tampilkan daftar jenis tes.
     */
    public function index(): Response
    {
        return Inertia::render('JenisTes/Index', [
            'jenisTes' => JenisTes::query()
                ->withCount('periodeTes')
                ->orderBy('id')
                ->get(),
        ]);
    }

    /**
     * Simpan jenis tes baru.
     */
    public function store(StoreJenisTesRequest $request): RedirectResponse
    {
        JenisTes::query()->create($request->validated());

        return redirect()->back()->with('success', 'Jenis tes berhasil ditambahkan.');
    }

    /**
     * Perbarui jenis tes.
     */
    public function update(UpdateJenisTesRequest $request, JenisTes $jenisTes): RedirectResponse
    {
        $jenisTes->update($request->validated());

        return redirect()->back()->with('success', 'Jenis tes berhasil diperbarui.');
    }

    /**
     * Hapus jenis tes yang belum dipakai pada periode.
     */
    public function destroy(JenisTes $jenisTes): RedirectResponse
    {
        if ($jenisTes->periodeTes()->exists()) {
            return redirect()->back()->with('error', 'Jenis tes tidak dapat dihapus karena sudah dipakai pada periode tes.');
        }

        $jenisTes->delete();

        return redirect()->back()->with('success', 'Jenis tes berhasil dihapus.');
    }
}
