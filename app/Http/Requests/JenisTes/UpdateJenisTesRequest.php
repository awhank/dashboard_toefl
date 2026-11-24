<?php

namespace App\Http\Requests\JenisTes;

use App\Models\JenisTes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJenisTesRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan aksi ini.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Normalisasi input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode' => strtoupper(trim((string) $this->input('kode'))),
            'nama' => trim((string) $this->input('nama')),
        ]);
    }

    /**
     * Dapatkan aturan validasi untuk data input.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var JenisTes $jenisTes */
        $jenisTes = $this->route('jenisTes');

        return [
            'kode' => [
                'required',
                'string',
                'max:10',
                Rule::unique('jenis_tes', 'kode')->ignore($jenisTes->id),
            ],
            'nama' => [
                'required',
                'string',
                'max:100',
            ],
            'is_resmi' => [
                'required',
                'boolean',
            ],
            'skor_bagian_min' => [
                'required',
                'integer',
                'min:0',
                'lte:skor_bagian_max',
            ],
            'skor_bagian_max' => [
                'required',
                'integer',
                'min:0',
                'gte:skor_bagian_min',
            ],
            'skor_total_min' => [
                'required',
                'integer',
                'min:0',
                'lte:skor_total_max',
            ],
            'skor_total_max' => [
                'required',
                'integer',
                'min:0',
                'gte:skor_total_min',
            ],
            'masa_berlaku_bulan' => [
                'nullable',
                'integer',
                'min:1',
                'max:255',
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
            'kode.required' => 'Kode jenis tes wajib diisi.',
            'kode.unique' => 'Kode jenis tes sudah digunakan.',
            'kode.max' => 'Kode jenis tes maksimal 10 karakter.',
            'nama.required' => 'Nama jenis tes wajib diisi.',
            'nama.max' => 'Nama jenis tes maksimal 100 karakter.',
            'is_resmi.required' => 'Status jenis tes wajib dipilih.',
            'skor_bagian_min.required' => 'Skor bagian minimal wajib diisi.',
            'skor_bagian_min.lte' => 'Skor bagian minimal tidak boleh lebih besar dari skor bagian maksimal.',
            'skor_bagian_max.required' => 'Skor bagian maksimal wajib diisi.',
            'skor_bagian_max.gte' => 'Skor bagian maksimal tidak boleh lebih kecil dari skor bagian minimal.',
            'skor_total_min.required' => 'Skor total minimal wajib diisi.',
            'skor_total_min.lte' => 'Skor total minimal tidak boleh lebih besar dari skor total maksimal.',
            'skor_total_max.required' => 'Skor total maksimal wajib diisi.',
            'skor_total_max.gte' => 'Skor total maksimal tidak boleh lebih kecil dari skor total minimal.',
            'masa_berlaku_bulan.integer' => 'Masa berlaku harus berupa angka bulan.',
            'masa_berlaku_bulan.min' => 'Masa berlaku minimal 1 bulan.',
            'masa_berlaku_bulan.max' => 'Masa berlaku maksimal 255 bulan.',
        ];
    }
}
