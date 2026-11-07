# Portofolio & Ringkasan Pengalaman Proyek (CV Ready)

Dokumen ini berisi rangkuman pencapaian teknis, deskripsi studi kasus, dan poin-poin pengalaman yang siap disalin (*copy-paste*) ke dalam **CV**, **Resume**, **Profil LinkedIn**, maupun **Website Portofolio**.

---

## 📌 Ringkasan Proyek (Elevator Pitch)
* **Nama Proyek**: Dashboard Hasil & Analitik TOEFL Pegawai
* **Institusi/Konteks**: Poltekkes Kemenkes Banjarmasin
* **Peran**: Full-Stack Web Developer / Software Engineer
* **Tipe Aplikasi**: Enterprise Internal Data Dashboard & Decision Support System (DSS)
* **Teknologi**: Laravel 13, Svelte 5 (Runes), Inertia.js v3, Tailwind CSS v4, MySQL 8.4, Docker Compose (Laravel Sail), TypeScript, Pest PHP

---

## 💼 Poin-Poin untuk CV / Resume (Bullet Points Siap Pakai)

Gunakan poin-poin di bawah ini pada bagian **Work Experience** atau **Projects** di CV Anda:

* **Merancang dan membangun sistem analitik terpusat** untuk pemantauan capaian target skor TOEFL (≥ 500) dan siklus masa berlaku sertifikat TOEFL ITP (2 tahun) bagi ±200 pegawai (Dosen dan Tenaga Kependidikan).
* **Mengembangkan arsitektur Single-Page Application (SPA) modern** menggunakan integrasi **Laravel 13**, **Inertia.js v3**, dan **Svelte 5 (Runes API)** tanpa memerlukan REST API terpisah, menghasilkan antarmuka reaktif berlatensi rendah.
* **Mengimplementasikan algoritma prioritas seleksi kandidat cerdas** (*Decision Support Engine*) di `StatusSertifikatService` untuk merekomendasikan pegawai yang wajib mengikuti tes periode berikutnya berdasarkan 4 tingkat urgensi: *Belum pernah tes ITP → Sertifikat Expired → Akan Expired (≤ 3 bulan) → Belum capai target skor*.
* **Mendesain logika evaluasi dinamis (*Zero Static Status Storage*)**, di mana status sertifikat dan capaian target dihitung secara real-time pada layer aplikasi berdasarkan tanggal acuan dinamis, menghindari inkonsistensi data di database.
* **Membangun modul Master Pegawai komprehensif** dengan otomasi pembuatan akun pengguna (`users`), sinkronisasi data kredensial, proteksi *rate-limiting*, siklus hidup nonaktif (*soft lifecycle with audit trail*), dan reset password instan.
* **Merancang sistem autentikasi & otorisasi multi-peran (RBAC)** dengan kebijakan wajib ganti password pada login pertama kali dan sanitasi akses rute untuk *Admin*, *Pimpinan*, dan *Pegawai*.
* **Mengonfigurasi dan mengorkestrasi container Docker** menggunakan **Laravel Sail** yang mengisolasi PHP 8.3, MySQL 8.4, dan phpMyAdmin untuk konsistensi lingkungan pengembangan lokal dan kemudahan *deployment*.

---

## 🛠️ Tech Stack & Keahlian yang Ditampilkan

| Kategori | Teknologi & Konsep |
|---|---|
| **Backend** | PHP 8.3 / Laravel 13, Eloquent ORM (Advanced Relationships, `hasOne()->ofMany()`, Transactions), Form Requests Validation, Custom Middlewares, Services Layer |
| **Frontend** | Svelte 5 (Runes: `$state`, `$derived`, `$effect`, `$props`), Inertia.js v3, TypeScript, Tailwind CSS v4, Modern SVG Data Visualization (Donut & Bar Charts) |
| **Database & Cache** | MySQL 8.4 (InnoDB, UTF-8 MB4 Unicode Collation), Database Migrations, Model Factories, Database Seeders |
| **Security** | Role-Based Access Control (RBAC), Password Hashing (Bcrypt), Brute Force Rate Limiting, Mandatory Password Rotation, Session Security |
| **DevOps & Tools** | Docker, Docker Compose, Laravel Sail, Vite 8 (`vite-plus`), WSL2, NPM, Git |
| **Testing & Quality** | Pest PHP, PHPStan (Larastan static analysis), Svelte-Check, Laravel Pint (PSR-12 coding standard) |

---

## 📖 Deskripsi Studi Kasus (Untuk Portofolio Website / LinkedIn Featured)

### 1. Latar Belakang & Masalah
Institusi pendidikan tinggi memiliki kuota terbatas dalam menyelenggarakan tes TOEFL periodik (1–2 kali setahun). Pimpinan dan bagian kepegawaian mengalami kesulitan dalam memetakan pegawai mana yang sertifikatnya sudah kadaluarsa, pegawai yang belum pernah mengikuti tes resmi (ITP), serta menentukan prioritas peserta tes periode berikutnya secara objektif dan transparan.

### 2. Solusi yang Dibangun
Membangun aplikasi dashboard analitik berbasis web yang bertindak sebagai sistem pendukung keputusan (*Decision Support System*):
1. **Real-time Analytical Dashboard**: Menampilkan metrik KPI utama (jumlah pegawai aktif, pegawai belum tes, persentase capaian target skor, dan sertifikat yang memerlukan perhatian) beserta grafik interaktif komposisi capaian target, status sertifikat, tren partisipasi per gelombang, dan rata-rata skor per bagian tes (*Listening, Structure, Reading*).
2. **Simulator Tanggal Acuan Fleksibel**: Pimpinan dapat memilih tanggal rencana pelaksanaan tes di masa depan untuk mensimulasikan status kadaluarsa sertifikat seluruh pegawai secara instan.
3. **Peringkat Kandidat Otomatis**: Menghasilkan daftar rekomendasi peserta berdasarkan tingkat urgensi kebutuhan tes.
4. **Sentralisasi Data Pegawai**: CRUD pegawai lengkap dengan kategorisasi Dosen/Tendik dan pengelolaan status kepegawaian yang aman.

### 3. Keunggulan Teknis & Arsitektur (Engineering Highlights)
* **Dynamic Query-time Computation**: Menolak menyimpan status "Expired" atau "Capai Target" secara statis di database. Status selalu dihitung saat kueri dijalankan sesuai tanggal acuan dan target skor tahun berjalan, menjamin integritas data 100% akurat kapan pun kebijakan tahunan berubah.
* **Clean Code & Service-Driven Architecture**: Memisahkan logika bisnis kompleks dari Controller ke dalam `StatusSertifikatService` dan `DashboardStatsService`, sehingga kode modular, mudah diuji (*testable*), dan mudah diperluas.
* **Modern Reactive UI**: Dibangun menggunakan Svelte 5 terbaru dengan paradigma *Runes*, menghasilkan bundel JavaScript ultra-ringan tanpa *virtual DOM overhead*, serta integrasi *seamless* Inertia.js v3 untuk pengalaman pengguna layaknya aplikasi desktop.
* **Auditability & Data Integrity**: Menerapkan kebijakan *zero hard delete* untuk entitas pegawai agar rekam jejak sertifikat dan riwayat skor tes historis tidak terputus.

---

## 📈 Log Progres & Milestone Proyek

* **Milestone 1 (Fondasi & Desain UI)**: Selesai
  * Perancangan skema database relasional (7 tabel utama).
  * Sistem desain UI terstandarisasi (palet warna brand, tipografi Nunito, layout sidebar responsif).
  * Autentikasi berbasis NIP dengan kebijakan keamanan rotasi password pertama.
* **Milestone 2 (Mesin Analitik & Integrasi Data Real-time)**: Selesai
  * Pembuatan `StatusSertifikatService` & `DashboardStatsService`.
  * Integrasi rute `/dashboard` ke database riil dengan filter tanggal acuan interaktif.
* **Milestone 3 (Manajemen Master Pegawai)**: Selesai
  * CRUD Master Pegawai terpadu di `resources/js/Pages/Pegawai/Index.svelte`.
  * Otomasi *provisioning* akun login pegawai baru dan sinkronisasi kredensial.
  * Fitur penonaktifan akun dengan alasan audit, pemulihan akun, dan reset password mandiri oleh Admin.
* **Milestone 4 (Impor Massal Excel Pegawai)**: Selesai
  * Fitur unggah berkas Excel daftar pegawai massal berbasis modal interaktif di Svelte 5.
  * Parser cerdas ganda di `ImportPegawaiService`: mendukung pembacaan langsung berkas resmi DUK Kemenkes (ekstraksi otomatis TTL dan gender dari 18 digit NIP) maupun Template Standar.
  * Otomasi *batch upsert* dengan transaksi database serta pembuatan akun login instan bagi setiap pegawai baru.
  * Fitur unduhan template Excel kustom siap pakai (`/pegawai/template-import`).
* **Milestone 5 (Manajemen Periode & Hasil Tes TOEFL)**: *Dalam Pengerjaan*
  * Modul Periode Tes & Input Hasil Tes TOEFL (impor nilai massal / manual).
  * Ekspor daftar kandidat prioritas ke Excel.

---
*Dokumen ini akan terus diperbarui secara otomatis seiring bertambahnya fitur signifikan pada proyek.*

