<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeriodeTes\StorePeriodeTesRequest;
use App\Http\Requests\PeriodeTes\UpdatePeriodeTesRequest;
use App\Models\JenisTes;
use App\Models\PeriodeTes;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PeriodeTesController extends Controller
{
    /**
     * Tampilkan daftar periode tes.
     */
    public function index(): Response
    {
        $periode = PeriodeTes::query()
            ->with('jenisTes:id,kode,nama,is_resmi')
            ->withCount('hasilTes')
            ->orderByDesc('tahun')
            ->orderByDesc('gelombang')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('PeriodeTes/Index', [
            'periode' => $periode,
            'jenisTes' => JenisTes::query()->orderBy('id')->get(['id', 'kode', 'nama', 'is_resmi']),
        ]);
    }

    /**
     * Simpan periode tes baru.
     */
    public function store(StorePeriodeTesRequest $request): RedirectResponse
    {
        PeriodeTes::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()?->id,
        ]);

        return redirect()->back()->with('success', 'Periode tes berhasil ditambahkan.');
    }

    /**
     * Perbarui periode tes.
     */
    public function update(UpdatePeriodeTesRequest $request, PeriodeTes $periodeTes): RedirectResponse
    {
        $periodeTes->update($request->validated());

        return redirect()->back()->with('success', 'Periode tes berhasil diperbarui.');
    }

    /**
     * Hapus periode tes yang belum memiliki hasil.
     */
    public function destroy(PeriodeTes $periodeTes): RedirectResponse
    {
        if ($periodeTes->hasilTes()->exists()) {
            return redirect()->back()->with('error', 'Periode tes tidak dapat dihapus karena sudah memiliki hasil tes.');
        }

        $periodeTes->delete();

        return redirect()->back()->with('success', 'Periode tes berhasil dihapus.');
    }
}
