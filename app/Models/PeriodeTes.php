<?php

namespace App\Models;

use Database\Factories\PeriodeTesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $jenis_tes_id
 * @property int $tahun
 * @property int $gelombang
 * @property string $nama
 * @property Carbon $tanggal_pelaksanaan
 * @property string|null $penyelenggara
 * @property string|null $keterangan
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'jenis_tes_id',
    'tahun',
    'gelombang',
    'nama',
    'tanggal_pelaksanaan',
    'penyelenggara',
    'keterangan',
    'created_by',
])]
class PeriodeTes extends Model
{
    /** @use HasFactory<PeriodeTesFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
        ];
    }

    /**
     * @return BelongsTo<JenisTes, $this>
     */
    public function jenisTes(): BelongsTo
    {
        return $this->belongsTo(JenisTes::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<HasilTes, $this>
     */
    public function hasilTes(): HasMany
    {
        return $this->hasMany(HasilTes::class);
    }

    /**
     * @return HasMany<ImportBatch, $this>
     */
    public function importBatches(): HasMany
    {
        return $this->hasMany(ImportBatch::class);
    }
}
