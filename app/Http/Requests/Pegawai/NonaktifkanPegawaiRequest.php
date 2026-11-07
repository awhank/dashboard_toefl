<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;

class NonaktifkanPegawaiRequest extends FormRequest
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
        return [
            'tanggal_nonaktif' => ['required', 'date'],
            'keterangan_nonaktif' => ['required', 'string', 'max:255'],
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
            'tanggal_nonaktif.required' => 'Tanggal nonaktif wajib diisi.',
            'tanggal_nonaktif.date' => 'Format tanggal nonaktif tidak valid.',
            'keterangan_nonaktif.required' => 'Alasan atau keterangan nonaktif wajib diisi.',
            'keterangan_nonaktif.max' => 'Keterangan nonaktif maksimal 255 karakter.',
        ];
    }
}

