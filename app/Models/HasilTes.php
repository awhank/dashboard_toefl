<?php

namespace App\Models;

use Database\Factories\HasilTesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $periode_tes_id
 * @property int $pegawai_id
 * @property int $skor_listening
 * @property int $skor_structure
 * @property int $skor_reading
 * @property int $skor_total
 * @property Carbon $tanggal_tes
 * @property Carbon|null $berlaku_sampai
 * @property string|null $no_sertifikat
 * @property 'impor'|'manual' $sumber_input
 * @property int|null $import_batch_id
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'periode_tes_id',
    'pegawai_id',
    'skor_listening',
    'skor_structure',
    'skor_reading',
    'skor_total',
    'tanggal_tes',
    'berlaku_sampai',
    'no_sertifikat',
    'sumber_input',
    'import_batch_id',
    'created_by',
    'updated_by',
])]
class HasilTes extends Model
{
    /** @use HasFactory<HasilTesFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_tes' => 'date',
            'berlaku_sampai' => 'date',
        ];
    }

    /**
     * @return BelongsTo<PeriodeTes, $this>
     */
    public function periodeTes(): BelongsTo
    {
        return $this->belongsTo(PeriodeTes::class);
    }

    /**
     * @return BelongsTo<Pegawai, $this>
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * @return BelongsTo<ImportBatch, $this>
     */
    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
