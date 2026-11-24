<?php

namespace App\Http\Requests\PeriodeTes;

use App\Models\PeriodeTes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePeriodeTesRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan aksi ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Dapatkan aturan validasi untuk data input.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var PeriodeTes $periodeTes */
        $periodeTes = $this->route('periodeTes');

        return [
            'jenis_tes_id' => [
                'required',
                'integer',
                'exists:jenis_tes,id',
            ],
            'tahun' => [
                'required',
                'integer',
                'between:2000,2100',
            ],
            'gelombang' => [
                'required',
                'integer',
                'between:1,2',
                Rule::unique('periode_tes', 'gelombang')
                    ->where('jenis_tes_id', $this->input('jenis_tes_id'))
                    ->where('tahun', $this->input('tahun'))
                    ->ignore($periodeTes->id),
            ],
            'nama' => [
                'required',
                'string',
                'max:100',
            ],
            'tanggal_pelaksanaan' => [
                'required',
                'date',
            ],
            'penyelenggara' => [
                'nullable',
                'string',
                'max:150',
            ],
            'keterangan' => [
                'nullable',
                'string',
            ],
        ];
    }

    /**
     * Pesan validasi dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis_tes_id.required' => 'Jenis tes wajib dipilih.',
            'jenis_tes_id.exists' => 'Jenis tes yang dipilih tidak valid.',
            'tahun.required' => 'Tahun periode wajib diisi.',
            'tahun.integer' => 'Tahun periode harus berupa angka.',
            'tahun.between' => 'Tahun periode harus berada di antara 2000 dan 2100.',
            'gelombang.required' => 'Gelombang wajib diisi.',
            'gelombang.integer' => 'Gelombang harus berupa angka.',
            'gelombang.between' => 'Gelombang harus 1 atau 2.',
            'gelombang.unique' => 'Periode dengan jenis tes, tahun, dan gelombang yang sama sudah ada.',
            'nama.required' => 'Nama periode wajib diisi.',
            'nama.max' => 'Nama periode maksimal 100 karakter.',
            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan wajib diisi.',
            'tanggal_pelaksanaan.date' => 'Format tanggal pelaksanaan tidak valid.',
            'penyelenggara.max' => 'Penyelenggara maksimal 150 karakter.',
        ];
    }
}
