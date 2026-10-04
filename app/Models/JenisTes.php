<?php

namespace App\Models;

use Database\Factories\JenisTesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $kode
 * @property string $nama
 * @property bool $is_resmi
 * @property int $skor_bagian_min
 * @property int $skor_bagian_max
 * @property int $skor_total_min
 * @property int $skor_total_max
 * @property int|null $masa_berlaku_bulan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'kode',
    'nama',
    'is_resmi',
    'skor_bagian_min',
    'skor_bagian_max',
    'skor_total_min',
    'skor_total_max',
    'masa_berlaku_bulan',
])]
class JenisTes extends Model
{
    /** @use HasFactory<JenisTesFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_resmi' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PeriodeTes, $this>
     */
    public function periodeTes(): HasMany
    {
        return $this->hasMany(PeriodeTes::class);
    }
}
