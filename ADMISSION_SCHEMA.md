# 📋 Skema Database: Modul Admission (PPDB)

> Hasil diskusi perancangan modul `app/Modules/Admission/`. Dokumen ini level
> **konsep skema**, bukan migration file (sesuai konvensi project — migration
> baru dijelaskan dulu di chat, dibuat manual oleh pemilik project).
>
> Prinsip inti: data calon siswa **dikarantina** penuh di modul ini (identitas,
> dokumen, hasil tes, biaya masa pendaftaran). Begitu resmi diterima, data
> di-**copy** (bukan dipindah) ke `students`/`parents` — baris di Admission
> tetap ada selamanya sebagai histori/audit pendaftaran.

---

## 1. Periode & Gelombang

### `admission_periods`
Periode PPDB per tahun ajaran (payung paling atas, biasanya 1 per tahun ajaran).

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `academic_year_id` (FK `academic_years`, restrict) | 3 | Tahun ajaran yang dituju calon siswa |
| `name` | "PPDB 2026/2027" | |
| `registration_prefix` | "PPDB26" | Awalan buat generate `registration_number` applicant |
| `opens_at` | 2026-01-05 | |
| `closes_at` | 2026-06-30 | |
| `is_active` | true | |

**Fungsi:** wadah tahunan. Kuota & biaya *tidak* di sini — itu di level gelombang, karena bisa beda-beda per gelombang dalam 1 periode yang sama.

### `admission_batches`
Gelombang pendaftaran di dalam satu periode (Gelombang 1, Gelombang 2, dst).

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_period_id` (FK `admission_periods`, cascade) | 1 | |
| `name` | "Gelombang 1" | |
| `starts_at` | 2026-01-05 | |
| `ends_at` | 2026-02-28 | nullable — boleh dibiarkan terbuka sampai gelombang berikutnya dibuka manual |
| `is_active` | true | |

**Fungsi:** unit terkecil yang punya kuota & tarif sendiri. Gelombang 1 dan Gelombang 2 bisa beda kuota, beda biaya, beda tanggal — tanpa saling ganggu.

### `admission_batch_quotas`
Kuota kursi per gelombang, dipecah per grade level.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_batch_id` (FK `admission_batches`, cascade) | 1 | |
| `grade_level_id` (FK `grade_levels`, restrict) | 5 (TK-A) | |
| `quota` | 30 | |

Unique: `(admission_batch_id, grade_level_id)`

**Fungsi:** sumber kebenaran kuota. Juga jadi **sumber pilihan dropdown grade level** di formulir publik — grade level yang muncul cuma yang punya baris kuota di gelombang aktif. Ini yang mencegah SD nawarin "Kelas 6" ke calon pendaftar tanpa perlu logic if-else hardcoded per jenjang: admin PPDB SD cukup bikin 1 baris kuota ("Kelas 1"), otomatis cuma itu yang tampil.

---

## 2. Biaya Masa Pendaftaran (khusus Admission, applicant belum jadi Student)

### `admission_batch_application_fees`
Master biaya yang wajib dibayar **saat masih calon** — formulir, tes pihak ketiga (mis. psikotes), dll.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_batch_id` (FK `admission_batches`, cascade) | 1 | |
| `name` | "Biaya Formulir" / "Biaya Tes Psikolog" | |
| `amount` | 250000 | |
| `sort_order` | 1 | nullable |

**Fungsi:** daftar komponen biaya yang ditagihkan **sebelum** ada keputusan diterima/ditolak. Terpisah total dari biaya keanggotaan (bagian 5) karena applicant belum punya `student_id`, jadi gak bisa numpang `invoices` milik Finance (FK-nya restrict ke `students`).

---

## 3. Data Calon Siswa (Karantina)

### `applicants`
Jantung modul — data pribadi calon siswa + status alur. **Tidak** menyimpan skor tes atau data orang tua (dipisah ke tabel lain).

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_period_id` (FK, restrict) | 1 | |
| `admission_batch_id` (FK, restrict) | 1 | |
| `grade_level_id` (FK `grade_levels`, restrict) | 5 (TK-A) | Kelas yang dituju |
| `registration_number` | "PPDB26-0001" | unique, auto-generate |
| `full_name` | "Ahmad Fadhil" | |
| `nickname` | "Dhil" | nullable |
| `gender` | "L" | |
| `birth_date` | 2021-08-14 | |
| `applicant_guardian_id` (FK `applicant_guardians`, restrict) | 7 | |
| `status` | "waiting_list" | lihat state machine di bawah |
| `queue_number` | 3 | nullable, cuma diisi pas status = `waiting_list` |
| `re_registration_deadline_at` | 2026-03-10 | nullable |
| `submitted_at` | 2026-02-01 09:14 | nullable |
| `decided_at` | 2026-02-20 | nullable |
| `student_id` (FK `students`, nullable, nullOnDelete) | null → 44 (setelah diterima) | Jejak balik setelah convert |

**State machine `status`:**
```
draft → submitted → document_review → scheduled_test → tested
tested → passed_screening (staf putuskan lewat Action, bukan formula skor otomatis)
       → failed_screening → selesai (tetap tersimpan sbg histori)
passed_screening → (cek kuota grade_level) →
    ada kursi     → accepted → re_registration_pending
        → re_registration_completed → [trigger convert ke Student]
        → re_registration_expired → kursi lepas → [trigger promote waiting_list]
    kursi penuh   → waiting_list (queue_number di-assign FIFO by submitted_at,
                     di antara yang sudah passed_screening)
        → kursi kosong (dari expired/kuota nambah) → accepted (ulang siklus)
        → periode closes_at lewat, masih waiting_list → rejected ("kuota penuh")
```

**Fungsi:** representasi lengkap 1 calon siswa dari daftar sampai diputuskan, termasuk posisi di antrian waiting list.

### `applicant_guardians`
Data orang tua/wali calon siswa — **satu baris bisa dipakai banyak `applicants`** (kakak-adik daftar di gelombang/tahun berbeda, nomor HP sama → nempel ke guardian yang sama), persis pola `parents`↔`students` yang sudah ada.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 7 | |
| `phone_number` | "081234567890" | |
| `father_name` | "Budi Santoso" | nullable |
| `mother_name` | "Siti Aminah" | nullable |
| `address` | "Jl. Merdeka No. 10" | nullable |

**Fungsi:** dedup orang tua sejak titik pendaftaran (lewat `FindOrCreateApplicantGuardianByPhoneAction`, paralel `FindOrCreateParentByPhoneAction` di modul Student), bukan cuma pas konversi.

---

## 4. Dokumen

### `admission_document_types`
Master jenis dokumen yang bisa diminta saat pendaftaran.

| Kolom | Contoh |
|---|---|
| `id` | 1 |
| `code` | "akta_lahir" |
| `name` | "Akta Kelahiran" |

### `admission_document_type_jenjang`
Pivot — dokumen mana wajib di jenjang mana.

| Kolom | Contoh |
|---|---|
| `admission_document_type_id` (FK, cascade) | 1 |
| `jenjang_id` (FK `jenjang`, cascade) | 1 (TK) |

**Fungsi:** biar TK cuma diminta Akta+KK+Foto, sementara SD nanti bisa nambah Ijazah/NISN/BPJS/Imunisasi tanpa ubah struktur tabel — tinggal isi baris pivot baru.

### `applicant_documents`
Berkas yang diupload calon pendaftar.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `applicant_id` (FK, cascade) | 1 | |
| `admission_document_type_id` (FK, restrict) | 1 | |
| `file_path` | "admission/docs/akta-1.pdf" | |
| `status` | "verified" | pending / verified / rejected |
| `verified_by` (FK `users`, nullable) | 12 | |
| `verified_at` | 2026-02-03 | nullable |
| `notes` | "Foto buram, minta ulang" | nullable |

---

## 5. Tes / Screening

### `admission_test_types`
Master jenis tes/observasi.

| Kolom | Contoh |
|---|---|
| `id` | 1 |
| `name` | "Observasi Holistik" |
| `code` | "observasi_holistik" |
| `is_active` | true |

### `admission_test_type_jenjang`
Pivot — jenis tes mana relevan di jenjang mana (TK cukup 1 jenis observasi; SMA/SMK nanti bisa punya Tes Akademik + Tes Fisik + Wawancara sebagai baris terpisah, tanpa nyampur ke form input tes TK).

| Kolom | Contoh |
|---|---|
| `admission_test_type_id` (FK, cascade) | 1 |
| `jenjang_id` (FK `jenjang`, cascade) | 1 (TK) |

### `admission_tests`
Hasil tes/observasi per calon siswa — bisa banyak baris per applicant.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `applicant_id` (FK, cascade) | 1 | |
| `admission_test_type_id` (FK, restrict) | 1 | |
| `score` | null | nullable — TK sering gak butuh angka |
| `passed` | true | nullable |
| `notes` | "Sudah bisa baca, pintar ngomong, kuat fisik, pandai bergaul" | nullable |
| `assessed_by` (FK `users`, nullable) | 8 | |
| `assessed_at` | 2026-02-15 | nullable |

**Fungsi:** menampung hasil tes seragam maupun holistik/kualitatif. Keputusan "lulus atau tidak" (`applicants.status` → `passed_screening`/`failed_screening`) tetap **keputusan eksplisit staf lewat Action**, bukan dihitung otomatis dari `score` — karena buat TK itu sering judgment holistik, bukan threshold angka.

---

## 6. Pembayaran Masa Pendaftaran (khusus Admission)

### `admission_invoices`
Tagihan biaya pendaftaran (formulir dkk) untuk 1 applicant.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `applicant_id` (FK, cascade) | 1 | |
| `admission_batch_id` (FK, restrict) | 1 | |
| `total_amount` | 250000 | snapshot |
| `status` | "paid" | unpaid / paid |

### `admission_invoice_items`
Rincian tagihan — snapshot, immutable (sama filosofi `invoice_items` Finance: kalau admin ubah nominal `admission_batch_application_fees` di tengah jalan, applicant yang sudah ditagih duluan gak ikut berubah).

| Kolom | Contoh |
|---|---|
| `id` | 1 |
| `admission_invoice_id` (FK, cascade) | 1 |
| `name` | "Biaya Formulir" |
| `amount` | 250000 |

### `admission_payments`
Pembayaran yang masuk.

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_invoice_id` (FK, cascade) | 1 | |
| `payment_channel_id` (FK `payment_channels`, restrict) | 2 | reuse master data Finance |
| `amount` | 250000 | |
| `proof_file` | "admission/proof/bayar-1.jpg" | nullable |
| `verified_by` (FK `users`, nullable) | 12 | |
| `verified_at` | 2026-02-02 | nullable |
| `paid_at` | 2026-02-02 | |

---

## 7. Biaya Setelah Resmi Diterima (Full Integrasi ke Finance — TIDAK ada tabel payment baru)

### `admission_batch_enrollment_fees`
Nominal biaya keanggotaan yang spesifik per gelombang (uang pangkal Gelombang 2 boleh beda dari Gelombang 1).

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `admission_batch_id` (FK, cascade) | 1 | |
| `billing_type_id` (FK `billing_types` milik Finance, restrict) | 4 ("Uang Pangkal") | |
| `amount` | 10000000 | |

Unique: `(admission_batch_id, billing_type_id)`

Contoh isi (persis skenario yang kamu kasih):

| `admission_batch_id` | `billing_type_id` | `amount` |
|---|---|---|
| 1 (Gel. 1) | Uang Pangkal | 10.000.000 |
| 1 (Gel. 1) | Uang Buku | 500.000 |
| 1 (Gel. 1) | Uang Seragam | 500.000 |
| 1 (Gel. 1) | SPP | 350.000 |
| 2 (Gel. 2) | Uang Pangkal | 12.000.000 |
| 2 (Gel. 2) | Uang Buku | 500.000 |
| 2 (Gel. 2) | Uang Seragam | 500.000 |
| 2 (Gel. 2) | SPP | 350.000 |

**Fungsi:** satu-satunya tabel Admission yang "menyentuh" dunia keuangan pasca-diterima — dan cuma sebagai **sumber nominal**, bukan sistem tagih-bayar sendiri. Begitu `ConvertApplicantToStudentAction` jalan, baris-baris ini di-snapshot jadi 1 `invoices` + beberapa `invoice_items` **asli** di Finance (`student_id` = siswa baru, `academic_year_id` dari `admission_periods`, `period_month` = bulan masuk). Setelah itu, alur tagih/bayar/verifikasi 100% pakai mekanisme Finance yang sudah ada — Admission tidak ikut campur lagi.

⚠️ **Perlu dicek saat implementasi:** `invoices` unik per `(student_id, academic_year_id, period_month)`. Kalau ada job bulanan otomatis generate SPP, pastikan job itu skip bulan yang sudah ke-generate lewat welcome-invoice ini (SPP bulan masuk sudah dibundling di situ), supaya siswa baru gak double-tagih di bulan yang sama.

---

## 8. Audit

### `applicant_status_logs`
Histori tiap perubahan status — termasuk perubahan otomatis oleh sistem (auto-expired, auto-promote).

| Kolom | Contoh | Keterangan |
|---|---|---|
| `id` | 1 | |
| `applicant_id` (FK, cascade) | 1 | |
| `from_status` | "accepted" | nullable |
| `to_status` | "re_registration_expired" | |
| `changed_by` (FK `users`, nullable) | null | null = otomatis oleh sistem |
| `note` | "Batas daftar ulang lewat, kursi dilepas ke waiting list" | nullable |
| `created_at` | 2026-03-11 00:00 | |

**Fungsi:** jejak lengkap kenapa applicant sampai di status akhir tertentu — terutama krusial buat kasus gugur/waiting-list-maju yang kamu minta di awal, biar bisa diaudit bukan cuma dilihat status akhirnya.

---

## 9. Perubahan ke Tabel yang Sudah Ada

### `students` (tambah kolom)

| Kolom baru | Contoh | Keterangan |
|---|---|---|
| `applicant_id` (FK `applicants`, nullable, nullOnDelete) | 1 | Siswa lama/input manual tetap `NULL`; siswa dari PPDB kesambung balik ke riwayat pendaftarannya |

---

## Catatan Proses (Belum Jadi Tabel — Butuh Job/Scheduler)

- **Auto-expire re-registration**: job harian cek `applicants.re_registration_deadline_at` yang lewat & status masih `accepted`/`re_registration_pending` → ubah ke `re_registration_expired`, log ke `applicant_status_logs`, trigger promote waiting list.
- **Auto-promote waiting list**: begitu kursi lepas (expired atau kuota nambah manual), ambil `applicants` dengan `status=waiting_list` & `passed_screening=true`, urutkan `queue_number` ASC, promosikan yang paling depan → `accepted`, set `re_registration_deadline_at` baru.

Konsisten sama gaya "hitung ulang status dari data asli lewat scheduled Action" yang sudah dipraktikkan di Finance.
