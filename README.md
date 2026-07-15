# 🏫 School Management System & Learning Management System (LMS) API

[![Laravel Version](https://shields.io)](https://laravel.com)
[![Architecture](https://shields.io)]()
[![Code Style](https://shields.io)]()

Platform Backend LMS dan Tata Kelola Sekolah terintegrasi berbasis API. Sistem ini dirancang untuk menangani seluruh ekosistem sekolah, mulai dari penerimaan siswa baru, kegiatan akademik harian, ujian interaktif, hingga transaksi finansial non-tunai (*cashless*).

---

## 🏛️ Arsitektur Sistem: Modular Domain-Driven Design (DDD) Lightweight

Proyek ini menggunakan pendekatan **Modular Laravel dengan Prinsip DDD Ringan**. Kami memecah aplikasi monolitik konvensional menjadi "mini-aplikasi" mandiri yang terisolasi di dalam folder `app/Modules/`.

### 🎯 Manfaat Praktis Bagi Tim Developer

*   **Zero Merge Conflict:** Setiap developer fokus pada modul/domainnya masing-masing di dalam `app/Modules/NamaModul/`. Struktur ini meminimalkan konflik kode saat melakukan *merge branch* di Git.
*   **Single Responsibility (Action-Based):** Kami tidak menumpuk logika bisnis di Controller atau satu file Service yang raksasa. Proyek ini menggunakan **Action Class** (Satu Class = Satu Fitur). 
*   **Batas Data Tegas (Bounded Context):** Modul otonom seperti `Student` dan `Teacher` mengelola datanya sendiri. Modul luar (seperti `Canteen`) hanya bisa berinteraksi melalui interface resmi untuk menjaga integritas data.
*   **Siap Migrasi ke Microservices:** Jika salah satu modul (misalnya `Exam`) mengalami lonjakan trafik tinggi, folder modul tersebut dapat dipotong dan dipindahkan menjadi layanan *microservice* terpisah dengan sangat mudah.

---

## 📂 Anatomi Standar Sebuah Modul

Setiap modul di dalam `app/Modules/` wajib memiliki struktur folder homogen sebagai berikut:

```text
app/Modules/NamaModul/
├── 📁 Controllers/     # Menerima request HTTP & mengembalikan JSON Resource tunggal
├── 📁 Requests/        # Validasi input API (Form Request khusus modul)
├── 📁 Resources/       # Transformasi data / API Formatting (Mencegah kebocoran struktur DB)
├── 📁 Models/          # Eloquent Model yang terisolasi untuk internal modul
├── 📁 Actions/         # JANTUNG DDD (Tempat logika bisnis murni berada. 1 Class = 1 Fitur)
├── 📁 Providers/       # Bootstrapping modul (Mendaftarkan route, view, & database otomatis)
└── 📄 api.php          # Definisi endpoint API khusus domain modul ini
```

> 💡 **Aturan Emas:** Controller hanya bertindak sebagai pengatur lalu lintas (menerima input, memanggil Action, mengembalikan JSON). **Logika bisnis murni wajib berada di dalam folder Actions!**

---

## 🗺️ Peta Domain & Cakupan Modul LMS

Sistem dibagi menjadi 13 modul utama yang saling berkolaborasi:

```text
app/Modules/
├── 📁 Core/           # Master Data Statis: Jenjang, Tahun Ajaran, Semester, & Master Mapel (Kunci Utama Sistem)
├── 📁 Auth/           # Keamanan: Sistem Login, Register, serta Manajemen Role & Permission (Spatie)
├── 📁 Admission/      # PPDB: Landing page pendaftaran, formulir calon siswa, upload berkas, seleksi, & kelulusan pendaftaran
├── 📁 Student/        # Data Aktor: Khusus mengelola profil detail Siswa & Orang Tua/Wali, Catatan Kedisiplinan/BK, & Rekam Medis UKS
├── 📁 Teacher/        # Data Aktor: Khusus mengelola profil detail Guru, NIP, & Berkas Kompetensi (Otonom)
├── 📁 Academic/       # Manajemen Dinamis: Data Kelas, Rombel/Class Groups, Plotting Siswa ke Kelas, & kompilasi Raport akhir
├── 📁 Learning/       # Aktivitas Harian: Jadwal Pelajaran harian, Materi Belajar, & Tugas/Assignment biasa
├── 📁 Attendance/     # Presensi & Absensi: Mencatat kehadiran siswa/guru harian, izin, sakit, terintegrasi ke rapor
├── 📁 Exam/           # Fitur Kompleks: Bank Soal & Sistem Ujian Interaktif (Konsep mirip Google Form / Quizizz)
├── 📁 Finance/        # Finansial: Pembayaran SPP, Tabungan, & Saldo Digital Siswa
├── 📁 Notification/   # Sentralisasi: Broadcast notifikasi otomatis via WhatsApp / Email (e.g., Tagihan SPP)
├── 📁 ELibrary/       # Perpustakaan Digital: Manajemen buku fisik (peminjaman) & e-book pendukung materi ajar
└── 📁 Canteen/        # Kantin Digital: Manajemen merchant/stan kantin, menu makanan, dan transaksi cashless siswa
```

---

## ⚙️ Alur Kerja Pembuatan Fitur (Workflow)

Saat mendapatkan tugas untuk membuat fitur baru (Contoh: *Membuat Jadwal Pelajaran Baru*), ikuti standarisasi langkah berikut:

1.  **Validasi Input:** Buat Form Request di `app/Modules/Learning/Requests/CreateScheduleRequest.php`.
2.  **Logika Bisnis:** Buat Action tunggal di `app/Modules/Learning/Actions/CreateClassScheduleAction.php`. Taruh semua logika validasi konflik jadwal di sini.
3.  **Ekspos Endpoint:** Daftarkan route di `app/Modules/Learning/api.php` mengarah ke Controller terkait.
4.  **Format Output:** Gunakan API Resource di `app/Modules/Learning/Resources/ScheduleResource.php` untuk menstrukturkan JSON *response*.

---

## 🚀 Memulai Pengembangan (Getting Started)

### Prasyarat Sistem
*   PHP >= 8.2
*   Composer
*   MySQL >= 8.0 atau PostgreSQL

### Langkah Instalasi

1.  **Clone Repositori**
    ```bash
    git clone https://github.com
    cd lms-modular-backend
    ```

2.  **Instalasi Dependensi**
    ```bash
    composer install
    ```

3.  **Konfigurasi Lingkungan**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Sesuaikan pengaturan database, mail server, dan kredensial WhatsApp Gateway di file `.env`.*

4.  **Migrasi & Seed Data Master**
    ```bash
    php artisan migrate --seed
    ```
    *Perintah ini akan mengisi data awal pada modul `Core` (Tahun ajaran, mapel dasar) dan akun administrator pada modul `Auth`.*

5.  **Jalankan Server Lokal**
    ```bash
    php artisan serve
    ```
    API kini siap diakses melalui `http://127.0.0`.

---

## 📝 Konvensi Kode (Code Conventions)

*   **Naming Convention:** Nama file Action wajib menggunakan format kata kerja aktif. Contoh: `StoreAdmissionFormAction.php`, `ProcessSppPaymentAction.php`.
*   **Database Migrations:** File migrasi database tetap diletakkan di folder bawaan Laravel (`database/migrations/`) namun penamaannya wajib diberi prefix nama modul untuk memudahkan pelacakan. Contoh: `2026_01_01_000000_create_admission_tables.php`.
