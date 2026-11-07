<?php

namespace App\Http\Requests\Pegawai;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePegawaiRequest extends FormRequest
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
            'nomor_pegawai' => [
                'required',
                'string',
                'max:50',
                'unique:pegawai,nomor_pegawai',
                'unique:users,username',
            ],
            'jenis_nomor' => [
                'required',
                'string',
                Rule::in(['nip', 'nipppk', 'internal']),
            ],
            'nama' => [
                'required',
                'string',
                'max:150',
            ],
            'jenis_pegawai' => [
                'required',
                'string',
                Rule::in(['dosen', 'tendik']),
            ],
            'jenis_kelamin' => [
                'nullable',
                'string',
                Rule::in(['L', 'P']),
            ],
            'tanggal_lahir' => [
                'nullable',
                'date',
            ],
            'jabatan' => [
                'nullable',
                'string',
                'max:150',
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
            'nomor_pegawai.required' => 'Nomor pegawai wajib diisi.',
            'nomor_pegawai.unique' => 'Nomor pegawai sudah terdaftar dalam sistem.',
            'jenis_nomor.required' => 'Jenis nomor identitas pegawai wajib dipilih.',
            'nama.required' => 'Nama lengkap pegawai wajib diisi.',
            'jenis_pegawai.required' => 'Jenis pegawai (Dosen/Tendik) wajib dipilih.',
            'jenis_kelamin.in' => 'Pilihan jenis kelamin harus L (Laki-laki) atau P (Perempuan).',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
        ];
    }
}

