<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportPegawaiService
{
    /**
     * Impor data pegawai dari berkas Excel (.xlsx / .xls / .csv).
     *
     * @return array{
     *     total: int,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     errors: list<string>,
     * }
     */
    public function import(string|UploadedFile $file, bool $updateExisting = true): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => ['Berkas Excel kosong atau tidak dapat dibaca.'],
            ];
        }

        // Deteksi apakah format berkas adalah Laporan DUK Kemenkes atau Template Standar
        $isDukFormat = $this->isDukFormat($rows);

        $parsedData = $isDukFormat
            ? $this->parseDukFormat($rows)
            : $this->parseStandardFormat($rows);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($parsedData as $index => $item) {
            $nomorPegawai = $item['nomor_pegawai'];
            $nama = $item['nama'];

            if (empty($nomorPegawai) || empty($nama)) {
                continue;
            }

            try {
                DB::transaction(function () use ($item, $updateExisting, &$created, &$updated, &$skipped) {
                    /** @var Pegawai|null $existing */
                    $existing = Pegawai::query()->where('nomor_pegawai', $item['nomor_pegawai'])->first();

                    if ($existing) {
                        if ($updateExisting) {
                            $existing->update([
                                'jenis_nomor' => $item['jenis_nomor'],
                                'nama' => $item['nama'],
                                'jenis_pegawai' => $item['jenis_pegawai'],
                                'jenis_kelamin' => $item['jenis_kelamin'] ?? $existing->jenis_kelamin,
                                'tanggal_lahir' => $item['tanggal_lahir'] ?? $existing->tanggal_lahir,
                                'jabatan' => $item['jabatan'] ?? $existing->jabatan,
                            ]);

                            // Sinkronkan data nama ke akun user jika ada
                            if ($existing->user) {
                                $existing->user->update([
                                    'name' => $item['nama'],
                                ]);
                            }

                            $updated++;
                        } else {
                            $skipped++;
                        }
                    } else {
                        // Tambah pegawai baru
                        $pegawai = Pegawai::query()->create([
                            'nomor_pegawai' => $item['nomor_pegawai'],
                            'jenis_nomor' => $item['jenis_nomor'],
                            'nama' => $item['nama'],
                            'jenis_pegawai' => $item['jenis_pegawai'],
                            'jenis_kelamin' => $item['jenis_kelamin'],
                            'tanggal_lahir' => $item['tanggal_lahir'],
                            'jabatan' => $item['jabatan'],
                            'status_aktif' => true,
                        ]);

                        // Otomatis buat akun pengguna login
                        User::query()->create([
                            'pegawai_id' => $pegawai->id,
                            'username' => $pegawai->nomor_pegawai,
                            'name' => $pegawai->nama,
                            'password' => Hash::make($pegawai->nomor_pegawai),
                            'role' => 'pegawai',
                            'is_active' => true,
                            'password_changed_at' => null,
                        ]);

                        $created++;
                    }
                });
            } catch (\Throwable $e) {
                $barisKe = $index + 1;
                $errors[] = "Baris {$barisKe} ({$nomorPegawai} - {$nama}): " . $e->getMessage();
            }
        }

        return [
            'total' => count($parsedData),
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Periksa apakah baris data menggunakan format laporan DUK Kemenkes.
     *
     * @param array<int, array<int, mixed>> $rows
     */
    protected function isDukFormat(array $rows): bool
    {
        $sampleHeader = '';
        for ($i = 0; $i < min(10, count($rows)); $i++) {
            $sampleHeader .= ' ' . implode(' ', array_filter(array_map('strval', $rows[$i])));
        }

        return str_contains(strtoupper($sampleHeader), 'DAFTAR URUT KEPANGKATAN')
            || str_contains(strtoupper($sampleHeader), 'NIP BARU');
    }

    /**
     * Parsing baris data berformat Laporan DUK Kemenkes (seperti daftar_pegawai.xlsx).
     *
     * @param array<int, array<int, mixed>> $rows
     * @return list<array{
     *     nomor_pegawai: string,
     *     jenis_nomor: 'nip'|'nipppk'|'internal',
     *     nama: string,
     *     jenis_pegawai: 'dosen'|'tendik',
     *     jenis_kelamin: 'L'|'P'|null,
     *     tanggal_lahir: string|null,
     *     jabatan: string|null,
     * }>
     */
    protected function parseDukFormat(array $rows): array
    {
        $result = [];

        // Data pada DUK biasanya dimulai dari baris ke-8 (indeks 7)
        $startRow = 7;
        for ($i = 0; $i < min(15, count($rows)); $i++) {
            $col0 = trim((string) ($rows[$i][0] ?? ''));
            if ($col0 === '1' || $col0 === 1) {
                $startRow = $i;
                break;
            }
        }

        for ($i = $startRow; $i < count($rows); $i++) {
            $row = $rows[$i];

            // NIP Baru ada pada kolom ke-3 (indeks 2)
            $rawNip = trim((string) ($row[2] ?? ''));
            // Hapus kutip satu di awal jika ada (contoh: '19700125...)
            $nip = ltrim($rawNip, "' \t\n\r\0\x0B");

            // Nama ada pada kolom ke-2 (indeks 1)
            $rawNama = (string) ($row[1] ?? '');
            $namaParts = explode("\n", str_replace("\r", "", $rawNama));
            $nama = trim($namaParts[0] ?? '');

            // Jabatan ada pada kolom ke-6 (indeks 5)
            $jabatan = trim((string) ($row[5] ?? ''));

            if (empty($nip) || empty($nama)) {
                continue;
            }

            // Klasifikasi jenis pegawai berdasarkan jabatan
            $jenisPegawai = str_contains(strtolower($jabatan), 'dosen') ? 'dosen' : 'tendik';

            // Ekstrak tanggal lahir & jenis kelamin dari NIP 18 digit (jika valid)
            $tanggalLahir = null;
            $jenisKelamin = null;

            if (preg_match('/^(\d{4})(\d{2})(\d{2})\d{6}([12])\d{3}$/', $nip, $matches)) {
                $thn = $matches[1];
                $bln = $matches[2];
                $tgl = $matches[3];
                if (checkdate((int) $bln, (int) $tgl, (int) $thn)) {
                    $tanggalLahir = "{$thn}-{$bln}-{$tgl}";
                }
                $jenisKelamin = $matches[4] === '1' ? 'L' : 'P';
            }

            $result[] = [
                'nomor_pegawai' => $nip,
                'jenis_nomor' => 'nip',
                'nama' => $nama,
                'jenis_pegawai' => $jenisPegawai,
                'jenis_kelamin' => $jenisKelamin,
                'tanggal_lahir' => $tanggalLahir,
                'jabatan' => $jabatan ?: null,
            ];
        }

        return $result;
    }

    /**
     * Parsing berkas tabel Excel berformat Template Standar.
     *
     * @param array<int, array<int, mixed>> $rows
     * @return list<array{
     *     nomor_pegawai: string,
     *     jenis_nomor: 'nip'|'nipppk'|'internal',
     *     nama: string,
     *     jenis_pegawai: 'dosen'|'tendik',
     *     jenis_kelamin: 'L'|'P'|null,
     *     tanggal_lahir: string|null,
     *     jabatan: string|null,
     * }>
     */
    protected function parseStandardFormat(array $rows): array
    {
        $result = [];

        // Cari baris header yang berisi "nomor_pegawai" atau "nomor" atau "nip"
        $headerIndex = 0;
        $headers = [];

        foreach ($rows as $idx => $r) {
            $rowText = strtolower(implode(' ', array_filter(array_map('strval', $r))));
            if (str_contains($rowText, 'nomor') || str_contains($rowText, 'nip') || str_contains($rowText, 'nama')) {
                $headerIndex = $idx;
                $headers = array_map(fn ($val) => strtolower(trim((string) $val)), $r);
                break;
            }
        }

        // Peta index kolom
        $colMap = [
            'nomor_pegawai' => null,
            'jenis_nomor' => null,
            'nama' => null,
            'jenis_pegawai' => null,
            'jenis_kelamin' => null,
            'tanggal_lahir' => null,
            'jabatan' => null,
        ];

        foreach ($headers as $cIdx => $headerName) {
            if (str_contains($headerName, 'nomor') || str_contains($headerName, 'nip')) {
                $colMap['nomor_pegawai'] ??= $cIdx;
            }
            if (str_contains($headerName, 'jenis nomor') || str_contains($headerName, 'tipe nomor')) {
                $colMap['jenis_nomor'] = $cIdx;
            }
            if (str_contains($headerName, 'nama')) {
                $colMap['nama'] ??= $cIdx;
            }
            if (str_contains($headerName, 'kategori') || str_contains($headerName, 'jenis pegawai')) {
                $colMap['jenis_pegawai'] = $cIdx;
            }
            if (str_contains($headerName, 'kelamin') || str_contains($headerName, 'gender') || $headerName === 'l/p') {
                $colMap['jenis_kelamin'] = $cIdx;
            }
            if (str_contains($headerName, 'lahir')) {
                $colMap['tanggal_lahir'] = $cIdx;
            }
            if (str_contains($headerName, 'jabatan')) {
                $colMap['jabatan'] = $cIdx;
            }
        }

        // Jika tidak ditemukan mapping eksplisit, fallback ke urutan kolom default
        $colNomor = $colMap['nomor_pegawai'] ?? 0;
        $colJenisNomor = $colMap['jenis_nomor'] ?? 1;
        $colNama = $colMap['nama'] ?? 2;
        $colJenisPegawai = $colMap['jenis_pegawai'] ?? 3;
        $colKelamin = $colMap['jenis_kelamin'] ?? 4;
        $colLahir = $colMap['tanggal_lahir'] ?? 5;
        $colJabatan = $colMap['jabatan'] ?? 6;

        for ($i = $headerIndex + 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $rawNomor = trim((string) ($row[$colNomor] ?? ''));
            $nomor = ltrim($rawNomor, "' \t\n\r\0\x0B");

            $nama = trim((string) ($row[$colNama] ?? ''));
            if (empty($nomor) || empty($nama)) {
                continue;
            }

            $rawJenisNomor = strtolower(trim((string) ($row[$colJenisNomor] ?? 'nip')));
            $jenisNomor = in_array($rawJenisNomor, ['nip', 'nipppk', 'internal'], true) ? $rawJenisNomor : 'nip';

            $rawJenisPegawai = strtolower(trim((string) ($row[$colJenisPegawai] ?? 'dosen')));
            $jenisPegawai = str_contains($rawJenisPegawai, 'tendik') ? 'tendik' : 'dosen';

            $rawKelamin = strtoupper(trim((string) ($row[$colKelamin] ?? '')));
            $jenisKelamin = in_array($rawKelamin, ['L', 'P'], true) ? $rawKelamin : null;

            // Parsing tanggal lahir (bisa berupa teks YYYY-MM-DD atau serial excel)
            $tanggalLahir = null;
            $rawLahir = trim((string) ($row[$colLahir] ?? ''));
            if (!empty($rawLahir)) {
                try {
                    $tanggalLahir = Carbon::parse($rawLahir)->format('Y-m-d');
                } catch (\Throwable) {
                    $tanggalLahir = null;
                }
            }

            // Jika tanggal lahir atau kelamin kosong dan NIP 18 digit, ekstrak otomatis
            if (preg_match('/^(\d{4})(\d{2})(\d{2})\d{6}([12])\d{3}$/', $nomor, $matches)) {
                if (!$tanggalLahir && checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
                    $tanggalLahir = "{$matches[1]}-{$matches[2]}-{$matches[3]}";
                }
                if (!$jenisKelamin) {
                    $jenisKelamin = $matches[4] === '1' ? 'L' : 'P';
                }
            }

            $jabatan = trim((string) ($row[$colJabatan] ?? ''));

            $result[] = [
                'nomor_pegawai' => $nomor,
                'jenis_nomor' => $jenisNomor,
                'nama' => $nama,
                'jenis_pegawai' => $jenisPegawai,
                'jenis_kelamin' => $jenisKelamin,
                'tanggal_lahir' => $tanggalLahir,
                'jabatan' => $jabatan ?: null,
            ];
        }

        return $result;
    }

    /**
     * Hasilkan berkas template Excel standar untuk diunduh oleh pengguna.
     */
    public function generateTemplateResponse(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        // Header tabel
        $headers = [
            'A1' => 'Nomor Pegawai',
            'B1' => 'Jenis Nomor',
            'C1' => 'Nama Lengkap (beserta gelar)',
            'D1' => 'Kategori (dosen/tendik)',
            'E1' => 'Jenis Kelamin (L/P)',
            'F1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'G1' => 'Jabatan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Contoh baris 1
        $sheet->setCellValueExplicit('A2', '198001012005011001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'nip');
        $sheet->setCellValue('C2', 'Dr. H. Supriadi, S.Kep., Ns., M.Kep.');
        $sheet->setCellValue('D2', 'dosen');
        $sheet->setCellValue('E2', 'L');
        $sheet->setCellValue('F2', '1980-01-01');
        $sheet->setCellValue('G2', 'Lektor Kepala');

        // Contoh baris 2
        $sheet->setCellValueExplicit('A3', '198505122010012002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 'nip');
        $sheet->setCellValue('C3', 'Siti Rahmawati, S.Kom.');
        $sheet->setCellValue('D3', 'tendik');
        $sheet->setCellValue('E3', 'P');
        $sheet->setCellValue('F3', '1985-05-12');
        $sheet->setCellValue('G3', 'Pranata Komputer Ahli Pertama');

        // Styling Header
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F7F7E'], // Brand primary color
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ];

        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Auto width untuk setiap kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'template_import_pegawai.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}

