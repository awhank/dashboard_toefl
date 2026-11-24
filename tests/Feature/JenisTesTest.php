<?php

use App\Models\JenisTes;
use App\Models\PeriodeTes;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->admin->forceFill(['password_changed_at' => now()])->save();
});

test('admin dapat menambah jenis tes', function () {
    $response = $this->actingAs($this->admin)->post(route('jenis-tes.store'), [
        'kode' => 'ePt',
        'nama' => 'TOEFL EPT',
        'is_resmi' => false,
        'skor_bagian_min' => 31,
        'skor_bagian_max' => 68,
        'skor_total_min' => 310,
        'skor_total_max' => 677,
        'masa_berlaku_bulan' => null,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('jenis_tes', [
        'kode' => 'EPT',
        'nama' => 'TOEFL EPT',
        'is_resmi' => false,
    ]);
});

test('jenis tes yang sudah dipakai periode tidak dapat dihapus', function () {
    $jenis = JenisTes::factory()->create();
    PeriodeTes::factory()->create(['jenis_tes_id' => $jenis->id]);

    $response = $this->actingAs($this->admin)->delete(route('jenis-tes.destroy', $jenis));

    $response->assertRedirect();
    $this->assertDatabaseHas('jenis_tes', ['id' => $jenis->id]);
});
