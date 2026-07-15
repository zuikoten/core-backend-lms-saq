# 🏛️ Arsitektur Backend: Modular Domain-Driven Design (DDD)

Dokumen ini menjelaskan standar arsitektur backend berbasis API yang digunakan dalam proyek LMS ini. Kita menggunakan pendekatan **Modular Laravel dengan Prinsip DDD Ringan** untuk memastikan kode tetap rapi, mudah dirawat, dan minim konflik seiring berkembangnya aplikasi.

---

## 🎯 Mengapa Memilih Arsitektur Ini?

LMS ini memiliki banyak domain bisnis yang kompleks (Siswa, Akademik, Keuangan, Ujian, dll). Arsitektur ini dipilih karena memberikan keuntungan praktis langsung bagi tim:

1. **Zero Merge Conflict (Kerja Tim Maksimal)**
   Aplikasi dibagi menjadi "mini-aplikasi" mandiri di dalam folder `app/Modules/`. Developer bisa bekerja di modul berbeda secara bersamaan tanpa takut merusak atau menimpa kode satu sama lain saat _push_ ke Git.
2. **Satu Class, Satu Fitur (Action-Based)**
   Logika bisnis tidak ditumpuk di Controller atau satu file Service yang panjang. Kita menggunakan **Action Class**. Jika ada _bug_ di fitur pembuatan jadwal, Anda cukup membuka file `CreateClassScheduleAction.php`.
3. **Batas Data yang Tegas (Clean Boundaries)**
   Modul besar seperti `Academic` menggabungkan Kelas dan Mapel agar performa query cepat. Sementara aktor utama seperti `Student` dan `Teacher` dipisah agar otonom dan datanya tidak bisa diacak-ngacak oleh modul lain (seperti Kantin atau Perpustakaan).
4. **Siap Scale-Up (Future Proof)**
   Karena setiap modul terisolasi dengan baik berbasis API, jika di masa depan modul tertentu (misal: Ujian) harus dipisah menjadi _Microservice_ tersendiri karena trafik tinggi, kita bisa memotong folder tersebut dengan sangat mudah.

---

## 📂 Struktur Folder Standard Modul

Setiap modul di dalam `app/Modules/` wajib mengikuti struktur anatomi berikut:

```text
app/Modules/NamaModul/
├── 📁 Controllers/     # Menerima request API HTTP & mengembalikan JSON Resource
├── 📁 Requests/        # Validasi input API (Form Request)
├── 📁 Resources/       # Format output JSON (Mencegah kebocoran struktur tabel asli)
├── 📁 Models/          # Eloquent Model khusus internal modul ini
├── 📁 Actions/         # JANTUNG DDD (Tempat logika bisnis murni berada. 1 Class = 1 Fitur)
├── 📁 Providers/       # Mendaftarkan route api.php otomatis ke framework
└── 📄 api.php          # Definisi endpoint API khusus modul ini
```

---

## 🗺️ Peta Modul LMS

app/Modules/
├── 📁 Core/ # Master Data Statis: Jenjang, Tahun Ajaran, Semester, & Master Mapel (Kunci Utama Sistem)
├── 📁 Admission/ # PPDB: Landing page pendaftaran, formulir calon siswa, upload berkas, seleksi, & kelulusan pendaftaran
├── 📁 Academic/ # Manajemen Dinamis: Data Kelas, Rombel/Class Groups, Plotting Siswa ke Kelas, & kompilasi Raport akhir
├── 📁 Student/ # Data Aktor: Khusus mengelola profil detail Siswa & Orang Tua/Wali, Catatan Kedisiplinan/BK, & Rekam Medis UKS
├── 📁 Teacher/ # Data Aktor: Khusus mengelola profil detail Guru, NIP, & Berkas Kompetensi (Otonom)
├── 📁 Learning/ # Aktivitas Harian: Jadwal Pelajaran harian, Materi Belajar, & Tugas/Assignment biasa
├── 📁 Exam/ # Fitur Kompleks: Bank Soal & Sistem Ujian Interaktif (Konsep mirip Google Form / Quizizz)
├── 📁 Finance/ # Finansial: Pembayaran SPP, Tabungan, & Saldo Digital Siswa
├── 📁 Auth/ # Keamanan: Sistem Login, Register, serta Manajemen Role & Permission (Spatie)
├── 📁 Notification/ # Sentralisasi: Broadcast notifikasi otomatis via WhatsApp / Email (e.g., Tagihan SPP)
├── 📁 Attendance/ # Presensi & Absensi: Mencatat kehadiran siswa/guru harian, izin, sakit, terintegrasi ke rapor
├── 📁 ELibrary/ # Perpustakaan Digital: Manajemen buku fisik (peminjaman) & e-book pendukung materi ajar
└── 📁 Canteen/ # Kantin Digital: Manajemen merchant/stan kantin, menu makanan, dan transaksi cashless siswa

---

💡 _Pegang teguh prinsip ini: Controller hanya menerima input dan memberikan respon JSON. Logika bisnis wajib berada di dalam folder `Actions/`._
