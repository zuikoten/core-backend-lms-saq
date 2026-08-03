# 🎓 Modul Student

Modul ini mengelola data siswa dan orang tua/wali. Model `Student` & `ParentProfile` sudah ada dari awal project (migration + Eloquent Model sudah dibuat pemilik project); modul ini menambahkan Action, Controller, Request, Resource, dan view di atasnya — mengikuti pola modul Auth (Controller tipis, logika bisnis di `Actions/`).

---

## Kenapa `ParentProfile`, Bukan `Parent`?

Nama class `Parent` **tidak bisa dipakai di PHP** — itu reserved word untuk `parent::method()` di pewarisan class. Makanya modelnya bernama `ParentProfile`, tapi tabelnya tetap `parents` (`protected $table = 'parents'`). Konsekuensi penting dari ini: relasi `hasMany()` di `ParentProfile::students()` **wajib** menyebutkan foreign key eksplisit (`'parent_id'`) — Laravel menebak FK dari nama *class*, bukan nama tabel, jadi kalau dibiarkan default akan salah tebak jadi `parent_profile_id`.

---

## Dua Sisi Akses (Konsisten dengan Modul Core)

| | Staf sekolah | Orang tua/wali |
|---|---|---|
| Guard | `web` | `sanctum` |
| Controller | `StudentController` | `StudentApiController` |
| Bisa apa | Full CRUD siswa & data orang tua | **Read-only** — cuma lihat data anak sendiri |

`StudentApiController::index()` **selalu** di-scope ke `parents.user_id = auth()->id()` — orang tua tidak mungkin mengakses data siswa lain lewat endpoint ini, bahkan lewat ID langsung sekalipun, karena query-nya dimulai dari `ParentProfile` milik user yang login, bukan dari `Student` langsung.

---

## Aturan Bisnis Utama: Satu Nomor HP = Satu Keluarga

**Masalah yang diselesaikan:** siswa kakak-adik seharusnya berbagi 1 data orang tua yang sama, bukan 2 baris `parents` terpisah — karena mekanisme aktivasi akun di modul Auth mencocokkan nomor HP ke **1 baris** `parents.phone_number`. Kalau ada duplikat, aktivasi jadi ambigu.

**Solusinya — `FindOrCreateParentByPhoneAction`:**
- Saat input siswa baru, nomor HP orang tua dicek dulu ke tabel `parents`.
- **Ketemu** → reuse baris itu, siswa baru ditautkan ke `parent_id` yang sama. Data nama/alamat yang diinput di form **tidak menimpa** data lama — cegah 1 keluarga punya data yang saling beda-beda gara-gara staf ketik ulang manual tiap kali input anak baru.
- **Tidak ketemu** → buat baris `parents` baru.
- Constraint `unique` di `parents.phone_number` (level database) menegakkan aturan ini juga di lapisan paling bawah, bukan cuma di kode aplikasi.

**Di sisi UI (form tambah siswa):** ada lookup AJAX (`FindParentByPhoneAction`, endpoint `students.parent-lookup`) yang jalan begitu staf selesai ngetik nomor HP — kalau nomor itu sudah terdaftar, field Nama Ayah/Ibu/Alamat **otomatis terisi & langsung readonly**. Ini supaya staf sadar dari awal (bukan baru tau pas submit) bahwa data ini bakal digabung ke keluarga yang sudah ada, dan mencegah input data yang berbeda untuk nomor HP yang sama.

---

## Kenapa Update Siswa & Update Orang Tua Dipisah 2 Form/Action?

`UpdateStudentAction` cuma pegang field milik `Student` sendiri (nama, NISN, status, dst). Perubahan data orang tua (`UpdateParentProfileAction`) sengaja dipisah, karena **1 orang tua bisa punya banyak anak** — kalau diubah lewat form 1 siswa, harus jelas bahwa perubahan itu berdampak ke *semua* saudara kandungnya, bukan efek samping yang tidak disadari. Di halaman edit siswa, kalau `parentProfile` itu punya >1 anak, ada peringatan eksplisit soal ini.

**`phone_number` tidak bisa diubah lewat `UpdateParentProfileAction`** — itu identitas login/aktivasi orang tua di modul Auth. Ganti nomor HP butuh alur tersendiri (verifikasi ulang, dsb.) yang belum dibangun; untuk sekarang masih manual lewat Tinker kalau memang dibutuhkan.

---

## Permission

Route staf dilindungi permission **`student.manage`** (di-seed lewat `PermissionSeeder`, konsisten penamaan singular per-domain seperti `core.manage`, bukan mengikuti nama tabel `students`).

---

## Struktur Folder

```text
app/Modules/Student/
├── Controllers/
│   ├── StudentController.php       # Staf, Blade, guard web — full CRUD
│   └── StudentApiController.php    # Orang tua, JSON, guard sanctum — read-only
├── Requests/
│   ├── StoreStudentRequest.php
│   ├── UpdateStudentRequest.php
│   └── UpdateParentProfileRequest.php
├── Resources/
│   ├── StudentResource.php
│   └── ParentProfileResource.php
├── Models/
│   ├── Student.php          # sudah ada dari awal project
│   └── ParentProfile.php    # sudah ada dari awal project
├── Actions/
│   ├── CreateStudentAction.php
│   ├── UpdateStudentAction.php
│   ├── UpdateParentProfileAction.php
│   ├── DeleteStudentAction.php
│   ├── FindOrCreateParentByPhoneAction.php   # dipakai saat submit form
│   └── FindParentByPhoneAction.php           # read-only, dipakai lookup AJAX
├── Providers/
│   └── StudentModuleServiceProvider.php
├── web.php
└── api.php
```

## Langkah Manual yang Sudah Dilakukan (dicatat, bukan instruksi ulang)

- Provider terdaftar di `bootstrap/providers.php`.
- Permission `student.manage` sudah di-seed.
- Constraint `unique` ditambahkan ke `parents.phone_number` lewat migration yang sudah ada (bukan migration baru).
- Link sidebar "Siswa & Guru" sudah diarahkan ke `route('students.index')`.

## Yang Sengaja Belum Dibangun

- Guard hapus siswa terhadap data yang mereferensikan `student_id` dari modul lain (invoice, tariff mapping) — menyusul begitu modul Finance mulai memakainya.
- Alur ganti nomor HP orang tua yang sudah aktivasi — masih manual lewat Tinker.
- Profil siswa yang lebih lengkap (foto, alamat, riwayat kesehatan/BK) — sesuai keputusan di `HANDOFF.md`, ini akan jadi **tabel terpisah** (`student_health_records`, `student_disciplinary_records`, dst.) begitu modul terkait digarap, bukan kolom tambahan di `students`.
- **Delegasi akses multi-akun untuk orang tua** (mis. ayah & ibu punya HP beda-beda, dua-duanya bisa pantau anak yang sama) — ini domainnya modul Auth (OTP invite, siapa boleh mengundang/mencabut), dicatat lengkap di bagian "Rencana Masa Depan" di `app/Modules/Auth/README.md`, bukan di sini.