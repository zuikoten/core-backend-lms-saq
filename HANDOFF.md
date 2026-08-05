# 📋 HANDOFF — Status Proyek & Agenda Selanjutnya

> Dokumen ini ditulis untuk diberikan ke sesi Claude (atau developer) yang baru,
> supaya langsung paham konteks tanpa perlu baca ulang seluruh riwayat chat
> sebelumnya. Untuk detail arsitektur teknis lengkap, baca `ARCHITECTURE.md`.
> Untuk detail teknis per modul, baca `README.md` di masing-masing folder
> modul (`app/Modules/{Nama}/README.md`) — dokumen ini sengaja tidak
> mengulang semua detail itu, cuma rangkuman status + keputusan lintas modul
> yang perlu diketahui sebelum lanjut kerja. **Untuk gaya penulisan kode,
> struktur, namespace, dan contoh konkret tiap lapisan (Model/Action/
> Controller/Request/Resource/Route/Blade), baca `STYLE_GUIDE.md`** —
> dibaca SEBELUM mulai menulis kode di modul apa pun, supaya tidak perlu
> minta contoh file dulu di awal seperti sesi-sesi sebelumnya.

---

## 🏫 Tentang Proyek

Sistem LMS + Tata Kelola Sekolah untuk **TK** (bagian dari ekosistem "SAQ"),
fokus utama akhirnya ke **Finance/SPP**, dibangun bertahap dari fondasi data
dulu. Urutan modul yang disepakati: **Core → Student → Academic → Finance**
(Teacher sengaja dilewati dulu, lihat bagian "Urutan Modul Selanjutnya").

- **Instalasi tunggal per sekolah** (bukan SaaS/multi-tenant) — tapi skema
  `jenjang` dirancang skalabel untuk sekolah yang punya lebih dari 1 jenjang
  pendidikan (PAUD/TK/SD/dst) di bawah 1 instalasi yang sama.
- **Laravel 13**, PHP 8.3, MySQL, testing pakai **Pest** (belum ada test
  otomatis untuk modul selain Auth — dicatat sebagai utang di tiap README
  modul)
- Arsitektur **Modular DDD ringan** — 1 modul = 1 folder mandiri di
  `app/Modules/`, logika bisnis di `Actions/` (1 class = 1 fitur), Controller
  cuma nerima input & kasih respons
- Frontend admin: **Blade + Tailwind + Alpine.js** (Vite), layout `layouts.staff`
- Frontend orang tua: **React terpisah**, konsumsi lewat API (Sanctum)

---

## ✅ Status Modul

### Auth — stabil, 7 test otomatis lolos
Login staf (email+password / OTP WA) & orang tua (OTP WA/password),
aktivasi akun orang tua by nomor HP, reset password, permission berbasis
Spatie. WhatsApp Gateway masih stub (log ke file). **Detail lengkap:**
`app/Modules/Auth/README.md` — termasuk bagian **"Rencana Masa Depan:
Delegasi Akses Multi-Akun"** (konsep matang, belum dieksekusi — supaya
ayah & ibu bisa pantau anak dari akun masing-masing tanpa gabung identitas).

### Core — CRUD lengkap, permission `core.manage`
5 entitas: **AcademicYear** (tahun ajaran, 1 aktif di seluruh sistem),
**Jenjang** (TK, dst — skalabel multi-jenjang), **GradeLevel** (TK-A/TK-B,
di bawah Jenjang), **Semester** (Ganjil/Genap per tahun ajaran, 1 aktif di
seluruh sistem, punya rentang tanggal nyata bukan cuma label), **Classroom**
(ruang kelas, master data lintas sistem — bisa dipakai >1 rombel sekaligus
karena ada shift). Semua staf-only (Blade) kecuali `AcademicYear` yang juga
punya API read-only untuk orang tua. **Detail:** `app/Modules/Core/README.md`.

### Student — CRUD lengkap, permission `student.manage`
`Student` & `ParentProfile` (model sudah ada dari awal project). Fitur
kunci: **auto-match orang tua by nomor HP** (`FindOrCreateParentByPhoneAction`)
— kakak-adik otomatis berbagi 1 data `parents`, dengan lookup AJAX di form
yang prefill & lock field kalau nomor sudah terdaftar. Update data siswa &
data orang tua sengaja 2 form terpisah (1 orang tua bisa punya banyak anak).
`phone_number` orang tua tidak bisa diubah lewat form biasa (itu identitas
login). Staf: full CRUD (Blade). Orang tua: read-only, cuma lihat anak
sendiri. **Detail:** `app/Modules/Student/README.md`.

### Academic — kerangka CRUD selesai, permission `academic.manage`
3 entitas: **ClassGroup** (Rombel, gabungan konsep "Kelas"+"Rombel" jadi 1
entitas), **ClassGroupStudent** (plotting siswa — **tabel HISTORI**, bukan
status tunggal, karena pindah rombel didukung dengan log lengkap
`moved_at`/`moved_out_at`), **ReportCard** (kerangka Rapor administratif:
catatan + status draft/published, **belum ada nilai per mapel** — nunggu
modul Exam/Learning). Staf: full CRUD + halaman "Plotting Siswa" khusus
(tempatkan/pindahkan). Orang tua: read-only, rombel aktif anak + rapor yang
sudah **published saja** (draft tidak pernah terlihat). **Detail:**
`app/Modules/Academic/README.md`.

### Finance — belum digarap
Migration tabelnya **sudah ada dari awal project** (`billing_types`,
`billing_tariffs`, `student_tariff_mappings`, `invoices`, `invoice_items`,
`payment_channels`, `invoice_payments`, `payment_gateway_transactions`,
`webhook_logs`). Tinggal Action/Controller/Resource-nya. **Konfirmasi
penting dari diskusi:** SPP ditagih **per bulan** (bukan per semester),
jadi `invoices.period_month` + `period_year` sudah cukup — **tidak perlu**
`semester_id` di invoice, meski tabel `semesters` sudah ada (dipakai Rapor,
bukan Finance).

### Modul lain (Teacher, Admission, Learning, Attendance, Exam,
Notification, ELibrary, Canteen) — belum digarap sama sekali, masih peta
jangka panjang di `ARCHITECTURE.md`.

---

## 🗺️ Urutan Modul Selanjutnya

```
✅ 1. Core      — Tahun Ajaran, Jenjang, Grade Level, Semester, Classroom
✅ 2. Student   — Siswa & Orang Tua/Wali
✅ 3. Academic  — Rombel, Plotting Siswa, kerangka Rapor
👉 4. Finance   — Pembayaran SPP (SELANJUTNYA)
```

**Teacher sengaja terus dilewati** — Finance tidak butuh data guru sama
sekali, dan `class_groups.homeroom_teacher_id` sudah disiapkan nullable
(kolom polos, belum FK constraint) supaya bisa diisi belakangan tanpa
migrasi ulang begitu modul Teacher akhirnya digarap.

---

## 🧭 Keputusan Lintas Modul yang Wajib Diikuti Konsisten

Ini pola-pola yang **berulang** di semua modul yang sudah dibangun — kalau
lanjut ke Finance atau modul lain, ikuti pola yang sama, jangan improvisasi
pola baru tanpa didiskusikan dulu:

- **Satu entitas "hanya 1 aktif di seluruh sistem"** (AcademicYear,
  Semester) → Action `Activate*Action` yang menonaktifkan semua yang lain
  dalam 1 `DB::transaction()` sebelum mengaktifkan yang dipilih.
- **Hapus data yang masih dipakai → `ValidationException::withMessages()`**,
  bukan biarkan error SQL mentah. Selalu guard di level Action, meski FK
  constraint di database juga sudah `restrictOnDelete()` (defense in depth).
- **Arah dependency SATU ARAH**: modul yang lebih "fondasi" (Core) **tidak
  pernah** import Model dari modul yang mengonsumsinya (Student, Academic).
  Guard yang perlu tahu data dipakai modul lain, query lewat `DB::table()`
  by nama tabel, bukan import Model cross-module.
- **Permission per-domain, singular, bukan ikut nama tabel**: `core.manage`,
  `student.manage`, `academic.manage` — bukan `core.manages`/`students.manage`/dst.
  Semua di-seed lewat `PermissionSeeder` (`database/seeders/`), **belum**
  ada CRUD Role & Permission lewat UI.
- **Staf = Blade (`guard: web`), Orang tua = API (`guard: sanctum`,
  read-only)** — Controller API orang tua SELALU di-scope dari
  `ParentProfile::where('user_id', auth()->id())`, tidak pernah query
  langsung dari sisi anak/data tanpa scope itu.
- **Layout Blade staf**: `layouts.staff` (BUKAN `layouts.app`). Ada
  component `x-status-badge` (varian success/warning/danger, TIDAK ada
  varian netral/abu-abu — untuk status "tidak aktif" pakai `<span>` manual)
  dan `x-icon-badge`.
- **Padding input**: `class="... px-3.5 py-2.5 text-sm ..."` — pernah
  kelewat di awal (bug berulang), jangan lupa lagi.
- **Registrasi Provider ke `bootstrap/providers.php` selalu manual** —
  Claude tidak punya akses ke file itu, selalu diingatkan tiap modul baru.
- **Migration selalu dibuat manual oleh pemilik project** — Claude cuma
  jelaskan skemanya di chat (development stage, `migrate:fresh` aman).

---

## 📝 Yang Sengaja Belum Dibangun (bukan bug, cuma dicatat)

- CRUD Role & Permission lewat UI — masih manual lewat seeder/Tinker.
- Provider WhatsApp Gateway final belum dipilih.
- **Delegasi akses multi-akun orang tua** (ayah & ibu login terpisah, lihat
  anak yang sama) — konsep matang di `app/Modules/Auth/README.md`, domain
  Auth (bukan Student), belum dieksekusi.
- **Permission granular level guru/wali kelas** (input nilai per mapel) —
  dicatat di `app/Modules/Academic/README.md`, nunggu modul Teacher.
- Validasi kapasitas `classrooms.capacity` terhadap jumlah siswa di rombel
  — kolomnya ada, baru informasi, belum divalidasi keras.
- Nilai per mata pelajaran di Rapor — nunggu modul Exam/Learning.
- Test otomatis (Pest) untuk semua modul selain Auth.

---

## 🎨 Keputusan Desain yang Perlu Diikuti Konsisten

- **Views**: `resources/views/modules/{modul}/...` (dot notation standar),
  layout & component shared di LUAR folder `modules/` (`layouts/`,
  `components/`)
- **JS**: dipisah per halaman, mirror 1:1 struktur folder `views/modules/`,
  auto-discover lewat glob di `vite.config.js`
- **CSS**: satu `app.css` global (Tailwind utility-first), tidak per halaman
- **Gaya visual**: referensi Spike Admin (Wrappixel) — card rounded-2xl,
  soft shadow, icon badge pastel, warna semantik (hijau=baik, kuning=perlu
  perhatian, merah=masalah), Tabler Icons, font Plus Jakarta Sans
- **Struktur folder modul**: boleh nambah folder di luar standar minimum
  (`Middleware/`, `Notifications/`, `Jobs/`, dll.) selama logika bisnis tetap
  di `Actions/` — lihat catatan di `ARCHITECTURE.md`

---

## 💬 Preferensi Kerja dengan Claude (penting!)

- **Development, bukan production** — kalau ada perubahan skema tabel yang
  perlu, CUKUP JELASKAN skemanya di chat, JANGAN buat file migration baru
  sendiri — pemilik project yang bikin filenya & `migrate:fresh`.
- **Pengiriman file:**
  - Modul yang masih **kosong/baru** (belum ada file sama sekali) → boleh
    dipaketkan **zip** sekali di awal, isinya cuma file yang Claude buat di
    sesi itu (bukan snapshot seluruh project — Claude tidak punya akses ke
    file yang sudah ada di komputer pemilik project).
  - Setelahnya, **revisi/tambahan kecil → kirim di chat**, satu per satu
    file dalam code block, lengkap path & status (baru/edit), BUKAN zip lagi.
    Pemilik project sering modifikasi manual sendiri di file-file itu.
- **Keputusan desain besar didiskusikan dulu** (pertanyaan singkat,
  bukan asumsi sepihak) — tapi begitu disepakati, eksekusi penuh tanpa
  ditunda. Termasuk: struktur skema tabel baru, penamaan permission,
  keputusan yang berdampak ke modul lain atau modul yang sudah stabil
  (terutama Auth).
- Pemilik project cukup teknis, terbuka didebat/dikoreksi kalau Claude
  salah asumsi (lihat riwayat: koreksi soal `ParentProfile` vs `parent_id`
  FK, penamaan permission, dll) — Claude sebaiknya tetap teliti baca kode
  yang sudah ada sebelum menambahkan sesuatu di atasnya, bukan menebak.
