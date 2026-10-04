# Analisis & Rencana — Dashboard Hasil TOEFL Pegawai Poltekkes Banjarmasin

> Dokumen ini adalah hasil diskusi analisis kebutuhan dan rancangan sistem. Diperbarui seiring progres —
> jangan hanya mengandalkan riwayat chat, dokumen ini adalah sumber kebenaran.

## Latar belakang
Poltekkes Banjarmasin (±200 pegawai: dosen + tendik) menyelenggarakan tes TOEFL 1–2 kali per tahun dengan kuota
peserta terbatas. Pimpinan membutuhkan dashboard untuk:
1. Mengetahui berapa pegawai yang telah mencapai target skor.
2. Menjadi dasar kebijakan **pemilihan peserta tes berikutnya** — memprioritaskan pegawai yang belum pernah tes ITP,
   sertifikatnya expired/akan expired (masa berlaku 2 tahun), atau belum mencapai target skor.

**Catatan penting:** proyek ini **tidak memakai Ollama/AI** — murni dashboard data & pelaporan.

## Proses bisnis
```
Master Pegawai (diperbarui tiap tahun; keluar/pensiun → nonaktif)
      │
      ▼
Admin buat Periode Tes (tahun, gelombang, tanggal, jenis tes) ──► Tes dilaksanakan (sebagian pegawai)
      │                                                                   │
      │                                                                   ▼
      │                                               Admin input hasil: impor Excel ATAU form manual
      ▼                                                                   │
Dashboard status pegawai ◄────────────────────────────────────────────────┘
      │
      ▼
Daftar kandidat peserta periode berikutnya → ekspor Excel → keputusan pimpinan (di luar aplikasi)
```

## Keputusan kebutuhan
| Aspek | Keputusan |
|---|---|
| Cakupan | Satu Poltekkes, ±200 pegawai; **tanpa struktur unit kerja** — cukup dibedakan Dosen/Tendik |
| Master pegawai | Dikelola admin (form + impor Excel), bertambah tiap tahun; nonaktif, tidak dihapus |
| Periode tes | Entitas tersendiri: tahun + gelombang (1–2x/tahun) + tanggal + jenis tes |
| Input hasil tes | Rutin setelah tiap pelaksanaan tes: **impor Excel** (massal) **dan form manual** (koreksi/susulan) |
| Isian hasil tes | Skor Listening, Structure & Written Expression, Reading, total (otomatis), nomor sertifikat |
| Jenis tes | TOEFL ITP (resmi) & TOEFL Prediction/EPT (informasi saja) |
| Dasar status & target | **Hanya ITP terbaru**; pegawai yang hanya punya Prediction = "Belum pernah tes ITP" |
| Masa berlaku | 2 tahun sejak tanggal tes ITP |
| Akan expired | Sisa masa berlaku ≤ 3 bulan terhadap tanggal acuan |
| Target skor | ≥ 500 saat ini; ditetapkan **per tahun** oleh admin (kebijakan bisa berubah) |
| Tampilan capaian | Dua kelompok: capai target vs belum capai target |
| Kandidat peserta | Daftar prioritas + ekspor Excel saja (keputusan akhir di luar aplikasi) |
| Peran | **Admin** (kelola data), **Pimpinan** (lihat semua, read-only), **Pegawai** (data sendiri) |
| Login | Username = nomor pegawai (NIP; tanpa NIP pakai NIPPPK/nomor internal), password default = nomor pegawai, wajib bisa diganti |
| AI/Ollama | Tidak dipakai |

## Aturan status (satu sumber kebenaran: `StatusSertifikatService`, belum diimplementasi)
Tanggal acuan default = hari ini; dapat diubah (mis. tanggal rencana periode berikutnya).
- **Belum pernah tes ITP** — tidak ada hasil ITP
- **Berlaku** — ITP terbaru, `berlaku_sampai` > tanggal acuan + 3 bulan
- **Akan expired** — tanggal acuan < `berlaku_sampai` ≤ tanggal acuan + 3 bulan
- **Expired** — `berlaku_sampai` ≤ tanggal acuan
- **Capai target** — skor ITP terbaru ≥ target tahun acuan (tanpa ITP → belum capai)

Prioritas kandidat (urutan default): belum pernah tes ITP → expired → akan expired → berlaku tapi belum capai target.

Status sertifikat & capai target **tidak disimpan di database** — dihitung saat query, karena bergantung pada
tanggal acuan dan target tahun berjalan (bisa berubah kapan saja).

## Database (sudah diimplementasikan — lihat `database/migrations/`)
Relasi: pegawai 1–0..1 users · pegawai 1–N hasil_tes · jenis_tes 1–N periode_tes · periode_tes 1–N hasil_tes ·
periode_tes 1–N import_batches · import_batches 1–N hasil_tes · users 1–N (created_by/updated_by).

| Tabel | Isi | Migration |
|---|---|---|
| `pegawai` | Master pegawai: nomor_pegawai (unik), jenis_nomor, nama, jenis_pegawai (dosen/tendik), tanggal_lahir, status_aktif | `2026_09_13_000001` + `2026_09_19_000001` (tambah `tanggal_lahir`) |
| `users` | Akun login: username = nomor_pegawai, role (admin/pimpinan/pegawai), password_changed_at | `0001_01_01_000000` (dimodifikasi dari default Laravel — email dihapus) |
| `jenis_tes` | ITP (resmi, masa berlaku 24 bulan) & PRED (informasi) | `2026_09_13_000003` |
| `periode_tes` | Setiap pelaksanaan tes: tahun + gelombang + jenis tes | `2026_09_13_000004` |
| `import_batches` | Riwayat impor Excel per periode | `2026_09_13_000005` |
| `hasil_tes` | Skor per pegawai per periode, sumber_input (impor/manual) | `2026_09_13_000006` |
| `target_skor` | Target skor minimal per tahun | `2026_09_13_000007` |

ITP terbaru per pegawai dihitung dengan window function `ROW_NUMBER() OVER (PARTITION BY pegawai_id ORDER BY tanggal_tes DESC)`.

## Modul aplikasi (rencana, belum semua diimplementasikan)
1. **Autentikasi** — login NIP (rate limited), ganti password wajib di awal, logout. *(sudah dibuat, lihat checklist — belum dicoba di browser)*
2. **Master data (Admin)** — pegawai (form + impor Excel), jenis tes, target skor per tahun, pengguna. *(belum dibuat)*
3. **Periode tes & input hasil (Admin)** — CRUD periode, impor Excel (pratinjau & validasi), form manual, daftar peserta. *(belum dibuat)*
4. **Dashboard (Admin, Pimpinan)** — KPI, grafik capai target, status sertifikat, tren per periode, rata-rata per bagian.
   **Halaman pratinjau desain sudah dibuat**: `resources/js/pages/Dashboard.svelte` (route sementara `/dashboard`,
   masih pakai data contoh, belum tersambung database).
5. **Kandidat peserta tes berikutnya** — daftar prioritas + ekspor Excel. *(belum dibuat)*
6. **Data pegawai** — tabel + status, riwayat tes. *(belum dibuat)*
7. **Profil (Pegawai)** — riwayat skor sendiri, ganti password. *(belum dibuat)*

## Desain UI (sudah diterapkan di halaman Dashboard pratinjau)
- **Nama aplikasi**: "Dashboard TOEFL Poltekkes Banjarmasin"
- **Gaya**: putih bersih + aksen — sidebar/header/latar putih, warna sekunder hanya di tombol, menu aktif, grafik
- **Palet warna** (`resources/css/app.css`, token `@theme inline`):
  - `--color-brand-teal: #2BAEAD`, `--color-brand-green: #7CB300` — dekorasi, ikon, seri grafik
  - `--color-primary: #1F7F7E` (teks putih di atasnya, kontras 4.8:1) — tombol utama & link
  - `--color-success: #4F7300` (kontras 5.5:1) — teks/badge hijau
  - Status: `--color-status-ok` (hijau), `--color-status-warning` (amber), `--color-status-danger` (merah), `--color-status-muted` (abu-abu), masing-masing punya varian `-bg` untuk latar badge
- **Tipografi**: heading `Nunito` weight 800 (`--font-heading`, kelas `.font-heading`); body `Segoe UI`/`system-ui` (`--font-sans`).
  Saat ini font Nunito dimuat lewat Google Fonts di `Dashboard.svelte` (butuh internet) — **untuk produksi, ganti ke
  self-host** (`@fontsource/nunito`, weight 800 saja) supaya tetap jalan di intranet kampus.
- **Logo & favicon**: `public/logo_polkessin.png` (logo penuh, dipakai saat sidebar terbuka). Sidebar diciutkan
  saat ini menampilkan placeholder huruf "P" — **menunggu file logo simbol saja** dari pengguna untuk versi final,
  juga untuk mengganti `favicon-32x32.png` dan `apple-touch-icon.png` yang saat ini belum ideal (masih logo lengkap/Laravel).
- **Komponen yang sudah dibuat** (hand-built, styling Tailwind manual — bukan library shadcn-svelte resmi):
  `resources/js/layouts/AppLayout.svelte` (sidebar collapsible + drawer mobile + header),
  `resources/js/components/Icon.svelte`, `StatCard.svelte`, `DonutChart.svelte`, `BarChart.svelte`,
  `resources/js/components/ui/Card.svelte`, `Badge.svelte`.
  *(Rencana awal memakai shadcn-svelte asli — untuk pratinjau cepat dipakai versi hand-built dulu; bisa diganti ke
  komponen shadcn-svelte resmi saat implementasi sungguhan agar dapat aksesibilitas & fitur lengkap seperti Sidebar
  primitive, Dialog, Data Table dari TanStack.)*
- **Menu per peran**: Admin = Dashboard, Kandidat Peserta, Data Pegawai, Periode Tes, Master (Jenis Tes, Target Skor,
  Pengguna), Profil · Pimpinan = sama minus Master · Pegawai = Profil & Riwayat Skor saja.

## Progres saat ini (checklist)
- [x] Analisis kebutuhan & keputusan bisnis
- [x] Rancangan database
- [x] Migration untuk semua tabel (`database/migrations/`)
- [x] Sistem desain UI (warna, font, layout) + halaman Dashboard pratinjau (data contoh, route `/dashboard`)
- [x] Perbaikan favicon di `resources/views/app.blade.php` (link ke file yang benar-benar ada)
- [x] Menjalankan migration ke MySQL (`php artisan migrate`) — dikonfirmasi berhasil, 10 tabel berstatus Ran (19 Sep 2026)
- [x] Model `User` (diperbaiki, sinkron dengan migration: username/role, bukan email) + `UserFactory`
- [x] Model `Pegawai` (`app/Models/Pegawai.php`) + `PegawaiFactory` — field nama, nomor_pegawai (NIP/NIPPPK/internal), jabatan sudah ada di migration
- [x] Model `JenisTes`, `PeriodeTes`, `ImportBatch`, `HasilTes`, `TargetSkor` + factory masing-masing
      (relasi lengkap: JenisTes→PeriodeTes→{HasilTes,ImportBatch}, Pegawai→HasilTes, semua `created_by`/`updated_by`→User)
- [x] Seeder: `JenisTesSeeder`, `UserSeeder` (admin/pimpinan), `TargetSkorSeeder` (500, 2023–2026),
      `PegawaiSeeder` (30 dummy: 20 dosen + 10 tendik + akun login masing-masing),
      `HasilTesSeeder` (6 periode ITP 2023–2025 + 1 periode Prediction, ~1/3 pegawai sengaja belum pernah tes ITP)
      — **belum dijalankan/dikonfirmasi**, tunggu `php artisan db:seed` oleh pengguna
- [x] Tambah kolom `tanggal_lahir` (nullable) di tabel `pegawai` — migration `2026_09_19_000001`,
      sudah disinkronkan ke Model, Fillable, cast, dan `PegawaiFactory`
- [~] **Perbaikan nama tabel eksplisit** (`protected $table`) — nama kelas model berbahasa Indonesia
      (mis. `JenisTes`, `TargetSkor`) tidak selalu ditebak Eloquent dengan benar (pluralisasi bahasa Inggris).
      Sudah diperbaiki: `Pegawai` (→ `pegawai`), `TargetSkor` (→ `target_skor`).
      **Masih perlu dicek/diperbaiki**: `JenisTes`, `PeriodeTes`, `HasilTes` (kemungkinan besar salah tebak
      karena berakhiran "Tes"); `ImportBatch` kemungkinan sudah benar (pluralisasi "Batch"→"Batches" wajar).
      Cek sebelum menjalankan seeder — kalau salah, `php artisan db:seed` akan error "table not found".
- [x] Autentikasi: login NIP (`auth/Login.svelte` + `LoginController` + `LoginRequest` dengan rate limit 5x),
      wajib ganti password di login pertama (`auth/GantiPassword.svelte` + `PasswordController` +
      middleware `EnsurePasswordIsChanged`), middleware peran (`EnsureUserHasRole`, alias `role:admin,pimpinan`),
      logout, redirect setelah login sesuai peran (pegawai→`/profil`, admin/pimpinan→`/dashboard`).
      `AppLayout` & `Dashboard` kini pakai data pengguna asli dari `page.props.auth.user` (bukan hardcoded).
      Halaman `Profil` baru placeholder minimal. **Belum dicoba login sungguhan di browser** — baru lolos
      `php -l`, Pint, dan `svelte-check` (0 error/warning di 191 file); PHPStan belum sempat dijalankan.
- [ ] Halaman Master Data (Pegawai, Jenis Tes, Target Skor, Pengguna)
- [ ] Halaman Periode Tes + impor Excel + form manual input hasil
- [ ] Sambungkan Dashboard ke data asli (`DashboardStatsService`)
- [ ] Halaman Kandidat Peserta Prioritas + ekspor Excel
- [ ] Halaman Data Pegawai & riwayat tes
- [ ] Halaman Profil pegawai
- [ ] `APP_NAME` di `.env` diubah ke "Dashboard TOEFL Poltekkes Banjarmasin" — **belum dilakukan oleh siapa pun, cek dulu**
- [ ] Contoh file Excel hasil tes dari pengguna — **masih ditunggu**, akan menentukan pemetaan kolom impor final
      (catatan: `templatedokumen/daftar_pegawai.xlsx` sudah ada — ini contoh **daftar pegawai**, bukan hasil tes;
      pengguna memilih untuk tidak diperiksa dulu — 19 Sep 2026)
- [ ] File logo simbol (untuk sidebar diciutkan & favicon) — **masih ditunggu** dari pengguna

## Hal yang masih menunggu dari pengguna
1. Contoh file Excel hasil tes TOEFL ITP yang biasa dipakai penyelenggara.
2. File logo versi simbol saja (PNG transparan ≥512px atau SVG) untuk sidebar diciutkan, favicon 32px, dan apple-touch-icon.
