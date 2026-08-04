# 🧩 Modul Core

Modul ini pemegang master data statis lintas modul: **Tahun Ajaran**, **Jenjang**, **Tingkat/Grade Level**, dan **Semester**. `Master Mapel` menyusul sesuai kebutuhan modul yang memakainya.

---

## Kenapa Ada 4 Entitas di Sini?

- **`academic_years`** — periode tahun ajaran (2026/2027, dst). Digarap paling awal karena hampir semua modul lain butuh referensi ini.
- **`jenjang`** — jenjang pendidikan (TK, dan berpotensi PAUD/SD/dst di masa depan). Sistem ini sekarang **fokus TK saja** (`HANDOFF.md`), tapi skemanya dirancang skalabel untuk sekolah yang mungkin punya lebih dari 1 jenjang di bawah 1 instalasi yang sama — **bukan** multi-tenant SaaS, tetap 1 instalasi per sekolah/yayasan.
- **`grade_levels`** — tingkat di *dalam* 1 jenjang (TK-A, TK-B). Terpisah dari `jenjang` karena keduanya level konsep yang beda: jenjang itu institusi/tingkat sekolah, grade level itu kelas di dalamnya.
- **`semesters`** — periode Ganjil/Genap di dalam 1 tahun ajaran, punya rentang tanggal nyata (`start_date`/`end_date`), bukan sekadar label. Awalnya dipertimbangkan cukup jadi kolom `enum` di tabel lain (mis. `report_cards.semester`), tapi diputuskan jadi tabel sendiri karena rencananya dipakai lintas modul (Rapor, dan kemungkinan besar Attendance/Learning nanti) yang butuh tau rentang tanggal aktualnya, bukan cuma label.

---

## Dua Sisi Akses

Sama seperti sebelumnya — staf (Blade, guard `web`) pegang CRUD penuh untuk keempat entitas. **Belum ada** endpoint API untuk orang tua di modul ini (beda dari `AcademicYear` yang sudah py punya `AcademicYearApiController`) — Jenjang/Grade Level/Semester itu murni data internal buat keperluan staf mengelola struktur sekolah, orang tua tidak perlu akses langsung ke sini. Kalau nanti orang tua perlu tau "anak saya di tingkat apa", itu didapat lewat relasi `Student → ClassGroup → GradeLevel` (modul Academic), bukan query langsung ke sini.

---

## Aturan Bisnis

- **Hanya 1 Tahun Ajaran aktif** & **hanya 1 Semester aktif** di seluruh sistem (bukan per-tahun-ajaran) — pola identik: `ActivateAcademicYearAction`/`ActivateSemesterAction` menonaktifkan yang lain dalam 1 transaction sebelum mengaktifkan yang dipilih.
- Tahun Ajaran & Semester yang sedang **aktif tidak bisa dihapus** — harus aktifkan yang lain dulu.
- **Jenjang yang masih punya Grade Level tidak bisa dihapus** — meski constraint DB-nya `cascadeOnDelete` (otomatis akan ikut kehapus), level aplikasi sengaja mencegah ini supaya penghapusan Grade Level jadi keputusan eksplisit, bukan efek samping.
- Nama Grade Level **unik per Jenjang**, bukan unik global — jadi 2 jenjang berbeda boleh kebetulan punya nama tingkat yang sama.

---

## Permission

Semua entitas di modul ini pakai **1 permission yang sama: `core.manage`** — karena semuanya 1 domain "master data struktural sekolah". Sengaja **tidak** dipisah jadi `academic-years.manage`, `jenjang.manage`, dst., beda dengan modul Student/Finance nanti yang levelnya lebih operasional harian.

⚠️ Seed lewat `PermissionSeeder`:
```php
Permission::firstOrCreate(['name' => 'core.manage', 'guard_name' => 'web']);
```

---

## Seeder

- `JenjangSeeder` — cuma seed "TK" (sesuai fokus sistem sekarang). Jenjang lain ditambahkan manual kalau memang dibutuhkan.
- `GradeLevelSeeder` — seed "TK-A", "TK-B" di bawah jenjang TK. **Harus jalan setelah** `JenjangSeeder`.
- `SemesterSeeder` — seed semester Ganjil (aktif) & Genap berdasarkan Tahun Ajaran yang **sedang aktif**. Kalau belum ada Tahun Ajaran aktif, seeder ini di-skip dengan warning, bukan bikin data asal. **Harus jalan setelah** `AcademicYearSeeder`.

Urutan `Jenjang→GradeLevel` dan `AcademicYear→Semester` itu 2 rantai independen, bebas urutan antar rantai, asal urutan **di dalam** masing-masing rantai tetap dijaga.

---

## Struktur Folder

```text
app/Modules/Core/
├── Controllers/
│   ├── AcademicYearController.php
│   ├── AcademicYearApiController.php   # read-only, orang tua
│   ├── JenjangController.php
│   ├── GradeLevelController.php
│   └── SemesterController.php
├── Requests/       Store/Update untuk masing-masing entitas
├── Resources/
│   └── AcademicYearResource.php        # satu-satunya yang perlu Resource (satu-satunya yang punya API)
├── Models/
│   ├── AcademicYear.php
│   ├── Jenjang.php
│   ├── GradeLevel.php
│   └── Semester.php
├── Actions/        Create/Update/Delete untuk masing-masing, + Activate untuk AcademicYear & Semester
├── Providers/
│   └── CoreModuleServiceProvider.php
├── web.php
└── api.php
```

## Yang Sengaja Belum Dibangun

- `Master Mapel` — menyusul begitu ada modul yang benar-benar butuh.
- Guard hapus Grade Level terhadap referensi dari `class_groups` (modul Academic) — menyusul begitu tabel itu dibuat.
- Endpoint API untuk orang tua di 3 entitas baru (Jenjang/Grade Level/Semester) — belum ada use case yang butuh akses langsung.
- Test otomatis (Pest).
