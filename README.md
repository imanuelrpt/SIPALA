# SIPALA — Sistem Pengaduan Lapangan

Aplikasi pengaduan masyarakat berbasis web untuk pemerintah daerah. Warga mengirim
laporan (aduan) lengkap dengan titik lokasi dan foto bukti, instansi daerah menindaklanjuti
melalui alur status yang terkontrol, dan administrator mengelola pengguna, instansi,
kategori, peta sebaran, serta laporan rekapitulasi.

Dibangun dengan **CodeIgniter 4** + **Bootstrap 5** + **MySQL**, mengikuti gaya penulisan
PSR-12 dan praktik keamanan web (CSRF, validasi server-side, kontrol akses berbasis peran,
unggahan aman, dan audit log).

---

## Daftar Isi

- [Fitur per Peran](#fitur-per-peran)
- [Teknologi](#teknologi)
- [Arsitektur](#arsitektur)
- [Prasyarat](#prasyarat)
- [Instalasi (Laragon / Windows)](#instalasi-laragon--windows)
- [Akun Demo](#akun-demo)
- [Struktur Direktori](#struktur-direktori)
- [Alur Status Aduan](#alur-status-aduan)
- [Pengujian](#pengujian)
- [Checklist Keamanan](#checklist-keamanan)
- [Deployment Produksi](#deployment-produksi)
- [Tangkapan Layar](#tangkapan-layar)
- [Catatan](#catatan)

---

## Fitur per Peran

### Masyarakat (warga)
- **Registrasi mandiri** — hanya dapat mendaftar sebagai `masyarakat`; peran tidak pernah
  dapat diubah dari masukan pengguna.
- **Membuat aduan** — judul, deskripsi, kategori, tanggal kejadian, titik koordinat
  (peta interaktif + pencarian alamat + "Gunakan lokasi saya"), alamat detail, kelurahan,
  kecamatan, kabupaten/kota.
- **Unggah foto bukti** — maksimal 3 berkas, JPG/PNG/WEBP, maksimal 2 MB per berkas,
  metadata EXIF dibersihkan otomatis.
- **Mode anonim** — sembunyikan identitas pelapor dari tampilan publik.
- **Aduan Saya** — daftar + filter (kata kunci, status, rentang tanggal) dan paginasi.
- **Detail aduan** — linimasa status, tanggapan petugas, riwayat, dan peta lokasi.
- **Menanggapi** dan **membatalkan** aduan selama belum selesai (dibatalkan = `ditolak`).
- **Memberi penilaian** 1–5 bintang atas pelayanan setelah aduan `selesai`.
- **Cek Status Aduan** (publik) — pelacakan dengan kode tiket tanpa harus masuk.
- **Lupa kata sandi** — token reset dengan masa berlaku terbatas.
- **Pengaturan akun** — ganti data profil dan kata sandi.

### Pemda (verifikator/tindak lanjut instansi)
- **Dasbor instansi** — ringkasan aduan milik instansi sendiri (kartu statistik + grafik).
- **Aduan Masuk** — daftar aduan untuk instansi yang terikat pada akunnya, dengan filter.
- **Detail aduan** — hanya aduan milik instansinya sendiri yang dapat dibuka.
- **Ubah status** mengikuti alur yang sah (lihat [Alur Status Aduan](#alur-status-aduan));
  status `ditolak` **wajib** disertai catatan alasan.
- **Tanggapan** ke pelapor.
- **Unggah foto bukti penyelesaian** (tersedia setelah status `diproses`).
- **Peta aduan** instansi dengan penanda berkelompok (marker cluster).
- **Pengaturan akun** — profil dan kata sandi.

### Admin
- **Dasbor menyeluruh** — total aduan, aduan selesai, total akun, rata-rata hari
  penyelesaian, komposisi status, tren 12 bulan, aduan per instansi, master data.
- **Manajemen pengguna** — buat akun **Pemda** (terikat instansi) dan **Masyarakat**,
  ubah, aktif/nonaktifkan, reset kata sandi. Peran hanya boleh ditetapkan di sini.
- **Manajemen instansi (dinas)** dan **kategori aduan** — termasuk pemetaan kategori ke
  instansi penanggung jawab.
- **Semua aduan** — lihat seluruh aduan, ubah status (termasuk *override*), dan
  **disposisi** (memindahkan aduan ke instansi lain).
- **Peta seluruh aduan** dengan filter status.
- **Laporan & rekapitulasi** sesuai filter, dengan **ekspor CSV**.
- **Log aktivitas** — jejak audit tindakan penting.
- **Pengaturan akun** — profil dan kata sandi.

### Publik (tanpa akun)
- Beranda dengan statistik ringkas dan aduan terbaru (identitas anonim tidak ditampilkan).
- Panduan penggunaan, Kebijakan Privasi (UU PDP), halaman Statistik agregat.
- Pelacakan status via kode tiket.

---

## Teknologi

| Lapisan | Pilihan |
| --- | --- |
| Bahasa | PHP 8.1+ (`declare(strict_types=1)`, type hints) |
| Framework | CodeIgniter 4.6 |
| Basis data | MySQL / MariaDB (driver `MySQLi`) |
| Front-end | Bootstrap 5.3.3, Bootstrap Icons 1.11.3 |
| Peta | Leaflet 1.9.4 + leaflet.markercluster 1.5.3 (OpenStreetMap) |
| Grafik | Chart.js 4.4.3 |
| Uji | PHPUnit 10.5 |
| Aset | Di-*vendor* lokal di `public/assets/` (tanpa CDN) |

> Semua pustaka front-end disimpan lokal sehingga aplikasi dapat berjalan di jaringan
> tertutup tanpa bergantung pada CDN eksternal.

---

## Arsitektur

Pola **thin controller — service — model**:

- **Controllers** (`app/Controllers`) tipis: hanya memvalidasi masukan, memanggil service,
  dan mengembalikan respons. Dikelompokkan per peran: `Publik`, `Auth`, `Masyarakat`,
  `Pemda`, `Admin`, `Files`.
- **Services** (`app/Services`) menampung logika bisnis: `AuthService`, `ComplaintService`,
  `UserService`, `AgencyService`, `CategoryService`, `ImageUploadService`,
  `LoginThrottleService`, `PasswordResetService`, `ReportService`, `ActivityLogService`.
- **Models** (`app/Models`) menangani akses data. Kolom istimewa (`role`, `status`,
  `agency_id`, `is_active`, `ticket_code`, …) **tidak** dimasukkan ke `$allowedFields`
  dan hanya ditulis melalui jalur terkontrol.
- **Enums & Entities** (`app/Enums`, `app/Entities`) untuk nilai peran/status/tindakan dan
  representasi objek.
- **Validation** (`app/Validation`) — aturan validasi terpisah per domain
  (`AuthRules`, `ComplaintRules`, `UserRules`, `AgencyRules`, `CategoryRules`,
  `TrackingRules`, `CustomRules`).
- **Filters** (`app/Filters`) — `AuthFilter`, `GuestFilter`, `RoleFilter`,
  `SecurityHeadersFilter`, plus filter bawaan `csrf`, `honeypot`, dan `invalidchars`.
- **Views** (`app/Views`) memakai layout + partial agar HTML tidak terduplikasi:
  `layouts/main.php`, `partials/*` (navbar, footer, flash, timeline, pager, peta, form
  tanggapan, lampiran, ringkasan aduan).
- **Language** (`app/Language/id`) — seluruh teks antarmuka berbahasa Indonesia.

Setiap operasi multi-tabel (misalnya membuat aduan beserta lampiran, unggah bukti
penyelesaian, disposisi) dijalankan dalam **transaksi basis data**.

---

## Prasyarat

- [Laragon](https://laragon.org/) (atau tumpukan web setara) dengan **PHP ≥ 8.1**,
  **MySQL/MariaDB**, dan **Composer**.
- Ekstensi PHP: `mysqli`, `mbstring`, `intl`, `gd` (untuk re-encode gambar), `fileinfo`,
  `json`.
- `mod_rewrite` aktif (agar URL tanpa `index.php`).

---

## Instalasi (Laragon / Windows)

1. **Letakkan proyek** di direktori web Laragon, misalnya `C:\laragon\www\sipala`.

2. **Pasang dependensi PHP:**

   ```bash
   composer install
   ```

3. **Siapkan berkas lingkungan** — salin templat lalu sesuaikan:

   ```powershell
   Copy-Item .env.example .env
   ```

   Sesuaikan minimal `app.baseURL` (mis. `http://sipala.test/` atau
   `http://localhost/sipala/public/`) dan kredensial basis data.

4. **Buat basis data** (lewat phpMyAdmin/HeidiSQL atau CLI):

   ```bash
   mysql -u root -e "CREATE DATABASE db_sipala CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

   Buat juga basis data untuk pengujian:

   ```bash
   mysql -u root -e "CREATE DATABASE db_sipala_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

5. **Bangkitkan kunci enkripsi:**

   ```bash
   php spark key:generate
   ```

6. **Jalankan migrasi dan seeder:**

   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```

7. **Buka aplikasi** di peramban:

   - Dengan virtual host Laragon: `http://sipala.test`
   - Tanpa virtual host (langsung ke folder `public`):
     `http://localhost/sipala/public/`

   > Seeder demo mengisi 1 admin, 5 instansi, 10 kategori, 10 akun pemda,
   > 8 akun masyarakat, dan 27 aduan contoh dengan koordinat nyata, riwayat status,
   > tanggapan, serta lampiran.

---

## Akun Demo

> **Kata sandi semua akun demo: `Sipala#2026`** — hanya untuk lingkungan lokal/pengembangan.
> Ganti atau hapus akun ini sebelum dipakai di produksi.

| Peran | Email |
| --- | --- |
| Admin | `admin@sipala.test` |
| Pemda — Dinas PUPR | `pupr1@sipala.test`, `pupr2@sipala.test` |
| Pemda — Dinas Perhubungan | `dishub1@sipala.test`, `dishub2@sipala.test` |
| Pemda — Dinas Lingkungan Hidup | `dlh1@sipala.test`, `dlh2@sipala.test` |
| Pemda — Dinas Dukcapil | `dukcapil1@sipala.test`, `dukcapil2@sipala.test` |
| Pemda — Dinas Kesehatan | `dinkes1@sipala.test`, `dinkes2@sipala.test` |
| Masyarakat | `warga1@sipala.test` … `warga8@sipala.test` |

---

## Struktur Direktori

```
sipala/
├── app/
│   ├── Config/            # konfigurasi (Routes, Sipala, Filters, Pager, Honeypot, ...)
│   ├── Controllers/       # controller tipis per peran
│   ├── Database/
│   │   ├── Migrations/    # 10 migrasi skema
│   │   └── Seeder/        # seeder data demo
│   ├── Entities/          # entitas domain
│   ├── Enums/             # UserRole, ComplaintStatus, ActivityAction, AttachmentType
│   ├── Filters/           # Auth, Guest, Role, SecurityHeaders
│   ├── Language/id/       # teks antarmuka (Indonesia)
│   ├── Models/            # akses data
│   ├── Services/          # logika bisnis
│   ├── Validation/        # aturan validasi per domain
│   └── Views/             # layout, partials, halaman per peran, error
├── docs/screenshots/      # tangkapan layar untuk dokumentasi
├── public/
│   ├── assets/            # Bootstrap, Icons, Leaflet, Chart.js (lokal)
│   └── index.php          # front controller
├── tests/
│   ├── feature/           # uji fitur (PHPUnit)
│   └── _support/          # basis uji (FeatureTestCase)
├── writable/
│   ├── session/
│   └── uploads/           # penyimpanan berkas aduan (di luar public/)
├── .env.example
├── composer.json
└── phpunit.xml.dist
```

---

## Alur Status Aduan

```
                 ┌───────────────────────────────────────────────┐
                 ▼                                               │
  [baru] ──► Menunggu ──► Diverifikasi ──► Diproses ──► Selesai   │ (ditolak ditinjau
                 │             │              │                  │  dari titik mana pun)
                 └─────────────┴──────────────┴──► Ditolak ◄──────┘
```

- Transisi berlaku: `menunggu → {diverifikasi, diproses, ditolak}`,
  `diverifikasi → {diproses, ditolak}`, `diproses → {selesai, ditolak}`.
- `ditolak` **wajib** disertai catatan alasan.
- Transisi yang tidak sah ditolak di sisi server (bukan hanya di UI).
- Admin berwenang mengubah status lintas instansi (*override*).
- Format kode tiket: `SIP-YYYYMMDD-XXXXXX`.

---

## Pengujian

Uji fitur berada di `tests/feature/` dan mencakup hal-hal berisiko keamanan:

- Registrasi publik **hanya** membuat akun `masyarakat`.
- Masuk / keluar (sesi diregenerasi saat login, dihancurkan penuh saat logout).
- Pembatasan percobaan login (5 percobaan / 15 menit).
- Akses lintas peran ditolak (**403**).
- **IDOR** — masyarakat tidak dapat membuka aduan milik orang lain.
- Pemda tidak dapat membuka aduan instansi lain.
- Transisi status tidak sah ditolak.
- Validasi koordinat (harus berada dalam wilayah Indonesia).
- Unggahan berbahaya / tipe berkas tidak diizinkan ditolak.

Jalankan:

```bash
# Seluruh suite
vendor/bin/phpunit

# Hanya uji fitur
vendor/bin/phpunit --no-coverage tests/feature
```

Pastikan basis data `db_sipala_test` sudah dibuat sebelum menjalankan pengujian.
Hasil terakhir: **62 uji, 174 asersi — OK**.

---

## Checklist Keamanan

- [x] **CSRF** aktif di seluruh form; header `X-CSRF-TOKEN` untuk permintaan AJAX.
- [x] **Escaping keluaran** dengan `esc()` pada semua data yang ditampilkan.
- [x] **CSP** (Content-Security-Policy) plus header keamanan lain
      (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, dll.).
- [x] **Query parameter binding** — tanpa penggabungan string SQL dari masukan pengguna.
- [x] **`$allowedFields` minimal** — kolom istimewa (`role`, `status`, `agency_id`,
      `is_active`, `ticket_code`) tidak dapat di-*mass assign*.
- [x] **Validasi server-side** menyeluruh via kelas aturan khusus.
- [x] **Unggahan aman** — daftar putih ekstensi (jpg/jpeg/png/webp), pemeriksaan MIME
      dengan `finfo`, batas 2 MB, maksimal 3 berkas/aduan, nama acak, disimpan **di luar**
      `public/`, dan gambar di-*re-encode* dengan GD (membersihkan EXIF).
- [x] **Sesi** — regenerasi ID saat login, penghancuran penuh saat logout, batas idle.
- [x] **Pembatasan login** — 5 percobaan / 15 menit per identitas.
- [x] **Pembatasan laju** — batas pengiriman aduan per jam.
- [x] **Honeypot** pada form publik (masuk, daftar, lupa password, pelacakan).
- [x] **Captcha opsional** (dapat diaktifkan lewat konfigurasi).
- [x] **Audit log** aktivitas penting.
- [x] **Privasi (UU PDP)** — halaman Kebijakan Privasi, mode anonim, data pelapor tidak
      ditampilkan pada halaman publik.
- [x] **Pesan kesalahan aman** — tanpa stack trace / detail internal pada produksi.
- [x] Berkas rahasia (`.env`) dan berkas unggahan **tidak** masuk kontrol versi.

---

## Deployment Produksi

1. Set `CI_ENVIRONMENT = production` pada `.env`.
2. Set `app.baseURL` ke domain HTTPS resmi dan `app.forceGlobalSecureRequests = true`.
3. Aktifkan CSP: `app.CSPEnabled = true`.
4. Jalankan `php spark key:generate` (kunci unik per lingkungan) dan `php spark migrate`.
5. Arahkan **document root** web server ke folder `public/`, bukan folder proyek.
6. Pastikan `writable/` dapat ditulis oleh proses web server, dan **tidak** dapat diakses
   langsung dari web.
7. Aktifkan HTTPS di web server (misalnya Let's Encrypt) dan `cookie.secure = true`.
8. Matikan toolkit debug / `logger.threshold` sesuai kebutuhan; jangan tampilkan
   halaman debug ke publik.
9. Hapus atau ganti seluruh akun demo, dan ubah kata sandi admin.
10. Konfigurasikan email SMTP (`email.*`) agar fitur lupa kata sandi berfungsi.
11. Cadangkan basis data dan folder `writable/uploads` secara berkala.

---

## Tangkapan Layar

Tangkapan layar tersedia di [`docs/screenshots/`](docs/screenshots/).

| Publik | |
| --- | --- |
| ![Beranda](docs/screenshots/01-beranda.png) | ![Masuk](docs/screenshots/02-masuk.png) |
| **Beranda** | **Masuk** |
| ![Panduan](docs/screenshots/03-panduan.png) | ![Statistik](docs/screenshots/04-statistik.png) |
| **Panduan** | **Statistik publik** |

| Masyarakat | |
| --- | --- |
| ![Dasbor masyarakat](docs/screenshots/05-masyarakat-dasbor.png) | ![Buat aduan](docs/screenshots/06-masyarakat-buat-aduan.png) |
| **Dasbor** | **Buat aduan** |
| ![Daftar aduan](docs/screenshots/07-masyarakat-daftar-aduan.png) | |
| **Aduan Saya** | |

| Pemda | |
| --- | --- |
| ![Dasbor pemda](docs/screenshots/08-pemda-dasbor.png) | ![Daftar aduan pemda](docs/screenshots/09-pemda-daftar-aduan.png) |
| **Dasbor instansi** | **Aduan Masuk** |
| ![Detail aduan](docs/screenshots/10-pemda-detail-aduan.png) | |
| **Detail aduan & tindak lanjut** | |

| Admin | |
| --- | --- |
| ![Dasbor admin](docs/screenshots/11-admin-dasbor.png) | ![Pengguna](docs/screenshots/12-admin-pengguna.png) |
| **Dasbor menyeluruh** | **Manajemen pengguna** |
| ![Laporan](docs/screenshots/13-admin-laporan.png) | ![Peta](docs/screenshots/14-admin-peta.png) |
| **Laporan & rekapitulasi** | **Peta sebaran aduan** |

---

## Catatan

- **URL lokal.** Virtual host `sipala.test` hanya tersedia bila dikonfigurasi pada
  Laragon/hosts. Bila tidak, aplikasi dapat langsung dibuka melalui
  `http://localhost/sipala/public/` (pastikan `app.baseURL` pada `.env` sesuai).
- **`app.indexPage = ''`** menghilangkan `index.php` dari URL dan memerlukan
  `mod_rewrite`. Bila modul itu tidak aktif, beri komentar pada baris tersebut sehingga
  URL memuat `index.php`.
- **Kata sandi basis data.** Untuk lingkungan lokal Laragon, `root` tanpa kata sandi adalah
  hal umum; jangan gunakan konfigurasi tersebut di produksi.
- **Laragon + MySQL.** Gunakan host `localhost` (bukan `127.0.0.1`) pada konfigurasi
  basis data lokal agar autentikasi berhasil.

---

## Lisensi

Kode aplikasi SIPALA dirilis untuk keperluan instansi pengguna. Kerangka dasar
CodeIgniter 4 mengikuti lisensi **MIT**.
