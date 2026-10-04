<?php

namespace App\Models;

use Database\Factories\TargetSkorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $tahun
 * @property int $skor_minimal
 * @property string|null $keterangan
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['tahun', 'skor_minimal', 'keterangan', 'created_by'])]
class TargetSkor extends Model
{
    /** @use HasFactory<TargetSkorFactory> */
    use HasFactory;

    protected $table = 'target_skor';

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
