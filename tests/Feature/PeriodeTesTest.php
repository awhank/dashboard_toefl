<?php

use App\Models\HasilTes;
use App\Models\JenisTes;
use App\Models\PeriodeTes;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->admin->forceFill(['password_changed_at' => now()])->save();
});

test('admin dapat menambah periode tes', function () {
    $jenis = JenisTes::factory()->itp()->create();

    $response = $this->actingAs($this->admin)->post(route('periode-tes.store'), [
        'jenis_tes_id' => $jenis->id,
        'tahun' => 2026,
        'gelombang' => 1,
        'nama' => 'TOEFL ITP 2026 Gelombang 1',
        'tanggal_pelaksanaan' => '2026-03-14',
        'penyelenggara' => 'Pusat Bahasa',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('periode_tes', [
        'jenis_tes_id' => $jenis->id,
        'tahun' => 2026,
        'gelombang' => 1,
        'nama' => 'TOEFL ITP 2026 Gelombang 1',
    ]);
});

test('periode tes yang sudah punya hasil tidak dapat dihapus', function () {
    $periode = PeriodeTes::factory()->create();
    HasilTes::factory()->create(['periode_tes_id' => $periode->id]);

    $response = $this->actingAs($this->admin)->delete(route('periode-tes.destroy', $periode));

    $response->assertRedirect();
    $this->assertDatabaseHas('periode_tes', ['id' => $periode->id]);
});
