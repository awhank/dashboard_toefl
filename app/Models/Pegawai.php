<?php

namespace App\Models;

use Database\Factories\PegawaiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $nomor_pegawai
 * @property 'nip'|'nipppk'|'internal' $jenis_nomor
 * @property string $nama
 * @property 'dosen'|'tendik' $jenis_pegawai
 * @property 'L'|'P'|null $jenis_kelamin
 * @property Carbon|null $tanggal_lahir
 * @property string|null $jabatan
 * @property bool $status_aktif
 * @property Carbon|null $tanggal_nonaktif
 * @property string|null $keterangan_nonaktif
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'nomor_pegawai',
    'jenis_nomor',
    'nama',
    'jenis_pegawai',
    'jenis_kelamin',
    'tanggal_lahir',
    'jabatan',
    'status_aktif',
    'tanggal_nonaktif',
    'keterangan_nonaktif',
])]
class Pegawai extends Model
{
    /** @use HasFactory<PegawaiFactory> */
    use HasFactory;

    protected $table = 'pegawai';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'tanggal_lahir' => 'date',
            'tanggal_nonaktif' => 'date',
        ];
    }

    /**
     * @return HasOne<User, $this>
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * @return HasMany<HasilTes, $this>
     */
    public function hasilTes(): HasMany
    {
        return $this->hasMany(HasilTes::class);
    }
}
