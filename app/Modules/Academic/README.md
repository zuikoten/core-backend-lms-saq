# 📚 Modul Academic

Modul ini mengelola Rombel (kelas/rombongan belajar), plotting siswa ke rombel, dan kerangka administratif Rapor. Ini modul ke-3 sesuai urutan `HANDOFF.md` (Core → Student → **Academic** → Finance), dibangun duluan meski Finance sebenarnya tidak wajib menunggunya (tarif SPP levelnya per-siswa, bukan per-kelas).

---

## Kenapa `ClassGroup`, Bukan "Kelas" atau "Rombel" Terpisah?

`ARCHITECTURE.md` menyebut "Kelas" dan "Rombel" seolah 2 hal, tapi setelah didiskusikan, keduanya disatukan jadi **1 entitas**: `class_groups`. 1 baris = 1 rombongan belajar spesifik di 1 jenjang + 1 tahun ajaran (mis. "TK-A — Melati" tahun 2026/2027). Nama rombel (`name`) sengaja cuma "Melati"/"Mawar" — konteks jenjang & tahun ajarannya didapat dari relasi (`gradeLevel`, `academicYear`), tidak diulang di nama.

---

## Dependency ke Modul Core (Penting)

Modul ini butuh 3 master data yang **ditambahkan ke modul Core** khusus untuk mendukung Academic:
- **`Jenjang`** → **`GradeLevel`** (`grade_levels`) — `class_groups.grade_level_id`
- **`Classroom`** (`classrooms`) — master data ruang/tempat belajar, sengaja diletakkan di Core (bukan Academic) karena berpotensi dipakai sistem lain di luar Academic (mis. aset/inventaris kelas nanti). Academic cuma salah satu **konsumen**-nya lewat `class_groups.classroom_id` (nullable — rombel boleh belum punya ruang tetap).
- **`Semester`** — `report_cards.semester_id`

**Arah dependency SATU ARAH**: Academic boleh import Model dari Core (`Modules\Core\Models\GradeLevel`, dst.), tapi Core **tidak pernah** import apa pun dari Academic. Guard-guard di Core yang perlu tahu "apakah data ini masih dipakai Academic" (`DeleteGradeLevelAction`, `DeleteAcademicYearAction`, `DeleteSemesterAction`) sengaja query lewat `DB::table('class_groups')`/`DB::table('report_cards')` by nama tabel, bukan import Model Academic — supaya Core tetap jadi fondasi yang berdiri sendiri, tidak bergantung ke modul yang mengonsumsinya.

---

## `class_group_students`: Tabel Histori, Bukan Status Tunggal

Ini keputusan yang paling berbeda dari pola CRUD biasa di modul lain. Awalnya direncanakan "1 siswa = 1 baris per tahun ajaran", tapi karena **pindah rombel di tengah tahun ajaran harus didukung dengan histori/log** (keputusan eksplisit), desainnya jadi tabel log:

- Baris dengan `moved_out_at IS NULL` = penempatan yang **sedang aktif**.
- Pindah rombel = tutup baris lama (`moved_out_at` diisi) + buka baris baru, dalam 1 transaction (`TransferStudentAction`).
- **Aturan "cuma 1 baris aktif per siswa per tahun ajaran" ditegakkan di level Action, bukan unique constraint database** — MySQL tidak punya cara bersih untuk partial unique index (unique yang cuma berlaku kalau kolom tertentu NULL) tanpa generated column. Kalau ada Action baru yang menyentuh tabel ini di masa depan, **wajib** lewat `AssignStudentToClassGroupAction`/`TransferStudentAction`, jangan insert manual ke `class_group_students`.
- Pindah rombel **dibatasi dalam 1 tahun ajaran yang sama** — pindah lintas tahun ajaran itu proses "kenaikan kelas" yang beda karakteristiknya (dan biasanya melibatkan hal lain: naik tingkat, dst.), belum dibangun di modul ini.

---

## Kerangka Rapor: Administratif Dulu, Nilai Menyusul

`report_cards` sekarang cuma punya `summary_notes` (catatan bebas wali kelas) + status `draft`/`published`. **Belum ada baris nilai per mata pelajaran** — itu nunggu modul Exam/Learning yang belum digarap sama sekali. Beberapa aturan penting:

- **`class_group_id` di rapor adalah SNAPSHOT**, diambil otomatis dari penempatan aktif siswa saat rapor dibuat (`CreateReportCardAction`). Kalau siswa pindah rombel setelahnya, rapor yang sudah dibuat **tidak ikut berubah** — tetap mengacu ke rombel saat itu, karena rapor adalah dokumen historis.
- **Rapor `published` tidak bisa diedit atau dihapus langsung** — mekanisme "unpublish" sengaja belum dibangun, supaya perubahan pasca-publish selalu jadi keputusan sadar (bukan potensi edit diam-diam padahal orang tua sudah bisa lihat).
- **Orang tua HANYA bisa lihat rapor berstatus `published`** — `ReportCardApiController` scope query-nya langsung `where('status', 'published')` dari awal, bukan disaring belakangan.

---

## Permission

Untuk sekarang, **1 permission untuk semua**: `academic.manage` (Rombel, plotting siswa, dan sisi administratif Rapor seperti buat/publish). Ini levelnya wakil kepala sekolah bagian kurikulum atau semacamnya.

**Rencana ke depan** (dicatat, belum dibangun): begitu modul **Teacher** ada, *input nilai per mata pelajaran* oleh guru/wali kelas akan pakai permission terpisah (mis. `report-card.input`), levelnya lebih sempit — guru cuma bisa input nilai untuk rombel yang dia ampu, bukan `academic.manage` penuh yang bisa kelola semua rombel.

---

## Dua Sisi Akses

| | Staf sekolah | Orang tua/wali |
|---|---|---|
| Guard | `web` | `sanctum` |
| Controller | `ClassGroupController`, `ClassGroupStudentController`, `ReportCardController` | `ClassGroupApiController`, `ReportCardApiController` |
| Bisa apa | Full CRUD Rombel, plotting/transfer siswa, kelola Rapor | **Read-only** — rombel aktif anak & rapor yang sudah published |

---

## Struktur Folder

```text
app/Modules/Academic/
├── Controllers/
│   ├── ClassGroupController.php         # Staf, Blade
│   ├── ClassGroupStudentController.php  # Staf, Blade — halaman plotting
│   ├── ReportCardController.php         # Staf, Blade
│   ├── ClassGroupApiController.php      # Orang tua, JSON, read-only
│   └── ReportCardApiController.php      # Orang tua, JSON, read-only
├── Requests/       Store/Update untuk ClassGroup & ReportCard, Assign/TransferStudentRequest
├── Resources/
│   ├── ClassGroupResource.php
│   └── ReportCardResource.php
├── Models/
│   ├── ClassGroup.php
│   ├── ClassGroupStudent.php   # tabel histori, lihat catatan di atas
│   └── ReportCard.php
├── Actions/
│   ├── CreateClassGroupAction.php / UpdateClassGroupAction.php / DeleteClassGroupAction.php
│   ├── AssignStudentToClassGroupAction.php   # penempatan pertama
│   ├── TransferStudentAction.php             # pindah rombel (histori)
│   └── CreateReportCardAction.php / UpdateReportCardAction.php / PublishReportCardAction.php / DeleteReportCardAction.php
├── Providers/
│   └── AcademicModuleServiceProvider.php
├── web.php
└── api.php
```

## Yang Sengaja Belum Dibangun

- Nilai per mata pelajaran di Rapor — nunggu modul Exam/Learning.
- Mekanisme "unpublish" rapor.
- Pindah rombel lintas tahun ajaran (proses "kenaikan kelas") — beda karakteristik dari transfer biasa, belum dibangun.
- Validasi kapasitas ruang kelas (`classrooms.capacity`) terhadap jumlah siswa di rombel — kolomnya ada, tapi baru informasi, belum divalidasi keras.
- `homeroom_teacher_id` di `class_groups` masih kolom polos tanpa FK constraint — jadi FK asli begitu modul Teacher digarap (additive, `ALTER TABLE`).
- Permission granular level guru/wali kelas untuk input nilai — lihat bagian Permission di atas.
- Test otomatis (Pest).
