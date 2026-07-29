# 🏫 School Management System & Learning Management System (LMS) API

[![Laravel Version](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel)](https://laravel.com)
[![Architecture](https://shields.io)]()
[![Code Style](https://shields.io)]()

Platform Backend LMS dan Tata Kelola Sekolah terintegrasi berbasis API. Sistem ini dirancang untuk menangani seluruh ekosistem sekolah, mulai dari penerimaan siswa baru, kegiatan akademik harian, ujian interaktif, hingga transaksi finansial non-tunai (*cashless*).

---

## 🏛️ Arsitektur Singkat

Proyek ini menggunakan pendekatan **Modular Laravel dengan Prinsip DDD Ringan** — aplikasi dipecah jadi "mini-aplikasi" mandiri di dalam `app/Modules/`, dengan logika bisnis terpusat di **Action Class** (1 Class = 1 Fitur), bukan menumpuk di Controller.

Dua jenis pengguna panel saat ini:
- **Staf sekolah** (superadmin, dan nanti guru/kepala sekolah/bendahara) — login lewat email+password atau OTP WhatsApp, sesi berbasis Blade (guard `web`), akses diatur lewat **permission** (Spatie), bukan nama role yang di-hardcode.
- **Orang tua/wali** — login lewat OTP WhatsApp atau password, dikonsumsi frontend React terpisah lewat API (guard `sanctum`). Akun diaktivasi sendiri dengan mencocokkan nomor HP ke data yang sudah diinput staf, bukan pendaftaran bebas.

> 📖 **Detail lengkap arsitektur** (struktur folder tiap modul, konvensi penamaan, folder tambahan yang diperbolehkan, dsb.) ada di [`ARCHITECTURE.md`](./ARCHITECTURE.md) — dokumen ini sengaja tidak mengulang semuanya di sini.

### Peta Modul

```text
app/Modules/
├── Core           Master Data Statis: Jenjang, Tahun Ajaran, Semester, Master Mapel
├── Auth           Login (email/password & OTP WhatsApp), aktivasi akun orang tua, Role & Permission (Spatie)
├── Admission      PPDB: pendaftaran, upload berkas, seleksi, kelulusan
├── Student        Profil Siswa & Orang Tua/Wali, Catatan Kedisiplinan/BK, Rekam Medis UKS
├── Teacher        Profil Guru, NIP, Berkas Kompetensi
├── Academic       Kelas, Rombel, Plotting Siswa, kompilasi Raport
├── Learning       Jadwal Pelajaran, Materi Belajar, Tugas/Assignment
├── Attendance     Presensi siswa/guru, izin, sakit
├── Exam           Bank Soal & Ujian Interaktif
├── Finance        Pembayaran SPP, Tabungan, Saldo Digital Siswa
├── Notification   Broadcast WhatsApp/Email (mis. Tagihan SPP)
├── ELibrary       Perpustakaan Digital (fisik & e-book)
└── Canteen        Kantin Digital, transaksi cashless
```

Belum semua modul di atas punya migration/model — baru **Auth**, **Finance**, dan **Student** (dasar) yang berjalan. Sisanya masih peta jangka panjang.

---

## 🚀 Memulai Pengembangan (Getting Started)

### Prasyarat
- PHP >= 8.3
- Laravel 13
- Composer
- Node.js + npm
- MySQL >= 8.0 (proyek ini dites pakai **Laragon**, sudah termasuk MySQL & **Mailpit**)

### Instalasi

1. **Clone & install dependency**
   ```bash
   git clone https://github.com/<org>/<repo>.git
   cd <repo>
   composer install
   npm install
   ```

2. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi database di `.env`**
   Buat database kosong dulu (mis. lewat HeidiSQL/phpMyAdmin bawaan Laragon), lalu isi:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_kamu
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Konfigurasi email/notifikasi untuk development**
   Sistem ini mengirim OTP WhatsApp dan email reset password — untuk development, **tidak perlu** gateway/mail server asli:
   ```env
   MAIL_MAILER=log
   ```
   atau kalau pakai Laragon, arahkan ke Mailpit bawaannya (`MAIL_MAILER=smtp`, `MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025`) supaya email reset password bisa dibuka lewat UI Mailpit (`http://localhost:8025`).

   > ⚠️ **OTP WhatsApp** saat ini pakai gateway stub (`LogWhatsappGateway`) — kode OTP-nya **tidak benar-benar terkirim ke WhatsApp**, tapi ditulis ke `storage/logs/laravel.log`. Buka file itu tiap kali butuh kode OTP saat testing, sampai provider WA final (Fonnte/Watzhap/dll.) dipasang.

5. **Migrasi & seed data**
   ```bash
   php artisan migrate --seed
   ```
   Perintah ini mengisi role & permission dasar (Spatie) serta akun `superadmin` awal (dibuat lewat seeder, bukan self-register — lihat `ARCHITECTURE.md` bagian Auth untuk alasannya).

6. **Compile aset frontend**
   Halaman Blade (login, dashboard, dll.) pakai Tailwind + Alpine.js yang di-build lewat Vite — **wajib** dijalankan sebelum tampilan bisa dilihat dengan benar:
   ```bash
   npm run dev
   ```
   Biarkan proses ini tetap jalan (watch mode) selama development — setiap ubah file di `resources/css` atau `resources/js` akan otomatis ke-compile ulang. Untuk build sekali jalan (mis. sebelum deploy): `npm run build`.

7. **Jalankan server lokal**
   ```bash
   php artisan serve
   ```
   Buka `http://localhost:8000/login`.

### Alur testing cepat
- Login **admin/staf**: `http://localhost:8000/login` (email+password dari seeder, atau tab "OTP WhatsApp" — kodenya cek di `storage/logs/laravel.log`)
- Lupa password: `http://localhost:8000/forgot-password` (pilihan email atau OTP WhatsApp)
- API **orang tua** (dikonsumsi React terpisah): prefix `http://localhost:8000/api/auth/...`

---

## ⚙️ Alur Kerja Pembuatan Fitur (Workflow)

Saat mendapat tugas fitur baru (contoh: *Membuat Jadwal Pelajaran Baru*):

1. **Validasi Input** — Form Request di `app/Modules/Learning/Requests/CreateScheduleRequest.php`
2. **Logika Bisnis** — Action tunggal di `app/Modules/Learning/Actions/CreateClassScheduleAction.php`
3. **Ekspos Endpoint** — daftarkan route di `app/Modules/Learning/api.php` (atau `web.php` kalau modul itu juga punya halaman Blade, seperti Auth)
4. **Format Output** — API Resource di `app/Modules/Learning/Resources/ScheduleResource.php`

---

## 📝 Konvensi Kode

- **Naming Action:** kata kerja aktif — `StoreAdmissionFormAction.php`, `ProcessSppPaymentAction.php`
- **Migration:** tetap di `database/migrations/` bawaan Laravel, prefix nama modul di nama filenya untuk memudahkan pelacakan — contoh: `2026_01_01_000000_create_admission_tables.php`
- **Akses/permission:** dicek lewat `can('nama.permission')`, bukan `hasRole('nama_role')` — supaya menambah role baru (guru, kepala sekolah, dll.) tidak perlu ubah kode, cukup assign permission yang relevan ke role itu di seeder