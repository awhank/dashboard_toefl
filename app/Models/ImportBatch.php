<?php

namespace App\Models;

use Database\Factories\ImportBatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $periode_tes_id
 * @property int $user_id
 * @property string $nama_file
 * @property int $jumlah_baris
 * @property int $jumlah_sukses
 * @property int $jumlah_gagal
 * @property 'selesai'|'dibatalkan' $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'periode_tes_id',
    'user_id',
    'nama_file',
    'jumlah_baris',
    'jumlah_sukses',
    'jumlah_gagal',
    'status',
])]
class ImportBatch extends Model
{
    /** @use HasFactory<ImportBatchFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<PeriodeTes, $this>
     */
    public function periodeTes(): BelongsTo
    {
        return $this->belongsTo(PeriodeTes::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<HasilTes, $this>
     */
    public function hasilTes(): HasMany
    {
        return $this->hasMany(HasilTes::class);
    }
}
