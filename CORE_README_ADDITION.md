
---

## Tambahan: `Classroom` (Ruang Kelas)

Master data ruang/tempat belajar. Diletakkan di modul Core (bukan Academic) karena sifatnya lintas sistem — berpotensi dipakai modul lain di luar Academic nanti (mis. aset/inventaris kelas). Academic (`class_groups.classroom_id`, nullable) cuma salah satu konsumennya.

- **Tidak ada guard shift/bentrok** — 1 ruang kelas boleh dipakai lebih dari 1 rombel (sekolah punya sistem shift pagi/siang), jadi sengaja tidak ada validasi "ruang ini sudah dipakai rombel lain".
- Hapus Classroom **aman** meski masih dipakai rombel — FK di `class_groups.classroom_id` pakai `nullOnDelete()` (beda dari `grade_level_id`/`academic_year_id` yang `restrictOnDelete()`), jadi rombel yang kehilangan ruangnya cuma jadi "belum ada ruang", bukan ikut terhapus atau memblokir penghapusan.

## Tambahan: Guard Cross-Module ke Modul Academic

Begitu modul Academic dibangun, 3 Action lama di modul ini dapat tambahan guard supaya tidak menghapus data yang masih dipakai Academic:

- `DeleteGradeLevelAction` — cek `class_groups` (via `grade_level_id`)
- `DeleteAcademicYearAction` — cek `class_groups` (via `academic_year_id`)
- `DeleteSemesterAction` — cek `report_cards` (via `semester_id`)

Semua guard ini **query lewat `DB::table(...)` by nama tabel**, bukan import Model dari `Modules\Academic`. Ini prinsip yang dijaga ketat: **Core adalah fondasi, tidak boleh depend ke modul yang mengonsumsinya** — arah dependency cuma boleh satu arah (Academic → Core, tidak pernah sebaliknya). Kalau ada modul baru lagi nanti yang mereferensikan `jenjang`/`academic_years`/`semesters`/`grade_levels`, guard serupa perlu ditambahkan dengan pola yang sama.
