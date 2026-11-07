<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardStatsService $dashboardStatsService,
    ) {}

    /**
     * Tampilkan halaman utama Dashboard TOEFL.
     */
    public function __invoke(Request $request): Response
    {
        $tanggalAcuan = $request->string('tanggal_acuan')->toString() ?: null;

        $data = $this->dashboardStatsService->getDashboardData($tanggalAcuan);

        return Inertia::render('Dashboard', $data);
    }
}

