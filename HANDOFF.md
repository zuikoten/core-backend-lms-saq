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
- Frontend orang tua: **React terpisah**, konsumsi lewat API (Sanctum) —
  **catatan penting**: sampai saat ini baru **endpoint API**-nya yang
  dibangun (Auth, Student minimal, Finance/Invoice lengkap). Halaman React-nya
  sendiri **belum ada satupun** — itu pekerjaan terpisah yang belum dimulai.

---

## ✅ Status Modul

### Auth — stabil, 7 test otomatis lolos
Login staf (email+password / OTP WA) & orang tua (OTP WA/password),
aktivasi akun orang tua by nomor HP, reset password, permission berbasis
Spatie. WhatsApp Gateway masih stub (log ke file). **Detail lengkap:**
`app/Modules/Auth/README.md` — termasuk bagian **"Rencana Masa Depan:
Delegasi Akses Multi-Akun"** (konsep matang, belum dieksekusi — supaya
ayah & ibu bisa pantau anak dari akun masing-masing tanpa gabung identitas).

**Bug & celah desain yang tercatat, belum semua diperbaiki:**
- ~~Request OTP aktivasi parent tidak validasi keberadaan data `parents`
  dulu~~ — **sudah diperbaiki** (`Rule::exists('parents','phone_number')
  ->whereNull('user_id')` kondisional di `RequestOtpRequest`, khusus
  `action_type=activation`).
- **Belum diperbaiki**: `ActivateParentAccountAction` commit transaksi
  (bikin user, link parent, assign role) **sebelum** controller bikin
  token Sanctum secara terpisah di luar transaksi itu — kalau pembuatan
  token gagal, akun sudah terlanjur aktif tapi user tidak dapat token &
  tidak bisa aktivasi ulang (stuck). Perbaikan diusulkan (perluas
  transaksi mencakup `createToken`) tapi menunggu konfirmasi pemilik
  project sebelum dieksekusi.

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
sendiri lewat `StudentApiController` (**masih sangat minimal** — cuma 1
endpoint `GET /api/students` pakai relasi `ParentProfile->students`, belum
ada endpoint detail/lain-lain). **Detail:** `app/Modules/Student/README.md`.

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

### Finance — ✅ ALUR MANUAL SUDAH LENGKAP END-TO-END
BillingType → PaymentChannel → BillingTariff → StudentTariffMapping (+
Bulk Mapping) → Invoice (generate massal SPP bulanan + manual untuk
tagihan sekali bayar) → InvoicePayment (pencatatan manual, status
invoice auto-recalculate) → 4 Laporan Keuangan (rekap bulanan, tunggakan,
per kanal, breakdown komponen biaya pakai aturan **FIFO**) → API
orang tua read-only (list & detail invoice, ringkasan tunggakan).

**Detail super lengkap ada di `app/Modules/Finance/README.md`** — baca itu
dulu sebelum lanjut kerja di modul ini, jangan tanya ulang hal yang sudah
didokumentasikan di situ (pola bulk action, urutan `@vite` vs `app.js`,
format `invoice_number`, aturan FIFO breakdown, dll).

**Satu-satunya yang tersisa**: integrasi **Finpay** (`PaymentGatewayTransaction`,
`WebhookLogs`) — skema tabel sudah ada dari awal project, providernya
belum final. Selain itu: download PDF invoice, endpoint "Bayar Sekarang",
dan **halaman React sisi orang tua belum dibangun sama sekali** (baru
endpoint API-nya).

### Modul lain (Teacher, Admission, Learning, Attendance, Exam,
Notification, ELibrary, Canteen) — belum digarap sama sekali, masih peta
jangka panjang di `ARCHITECTURE.md`.

---

## 🗺️ Progres & Agenda Selanjutnya

```
✅ 1. Core — Tahun Ajaran, Jenjang, Grade Level, Semester, Classroom
✅ 2. Student — Siswa & Orang Tua/Wali
✅ 3. Academic — Rombel, Plotting Siswa, kerangka Rapor
✅ 4. Finance — Alur manual lengkap (lihat detail di README modul)
👉 5. Finance   — Proses pembayaran + integrasi payment Gateway Finpay (SELANJUTNYA)
```

**Yang bisa jadi agenda selanjutnya** (belum ada urutan resmi disepakati,
diskusikan dulu sama pemilik project sebelum eksekusi):
- **Integrasi Finpay** — begitu provider final, tinggal isi
  `PaymentGatewayTransaction` & `WebhookLogs` di modul Finance yang sudah
  ada kerangkanya.
- **Halaman React sisi orang tua** — endpoint API Auth, Student
  (minimal), dan Finance/Invoice sudah siap dikonsumsi, pembuatan halaman React dibangun terpisah oleh tim lain dan sedang dalam progress.
- **Teacher** — sengaja terus dilewati sejauh ini. `class_groups.homeroom_teacher_id`
  sudah disiapkan nullable (kolom polos, belum FK constraint) supaya bisa
  diisi belakangan tanpa migrasi ulang.
- Modul lain (Admission/PPDB, Learning, Attendance, Exam, Notification,
  ELibrary, Canteen) — belum digarap sama sekali.

---

## 🧭 Keputusan Lintas Modul yang Wajib Diikuti Konsisten

Ini pola-pola yang **berulang** di semua modul yang sudah dibangun — kalau
lanjut ke modul manapun, ikuti pola yang sama, jangan improvisasi
pola baru tanpa didiskusikan dulu:

- **Satu entitas "hanya 1 aktif di seluruh sistem"** (AcademicYear,
  Semester) → Action `Activate*Action` yang menonaktifkan semua yang lain
  dalam 1 `DB::transaction()` sebelum mengaktifkan yang dipilih.
- **Hapus data yang masih dipakai → `ValidationException::withMessages()`**,
  bukan biarkan error SQL mentah. Selalu guard di level Action, meski FK
  constraint di database juga sudah `restrictOnDelete()` (defense in depth).
  Tapi kalau memang tidak ada tabel lain yang bergantung, tidak perlu
  dipaksa nambah guard kosongan (lihat contoh `DeleteStudentTariffMappingAction`
  di Finance).
- **Arah dependency SATU ARAH**: modul yang lebih "fondasi" (Core) **tidak
  pernah** import Model dari modul yang mengonsumsinya (Student, Academic,
  Finance). Guard yang perlu tahu data dipakai modul lain, query lewat
  `DB::table()` by nama tabel, bukan import Model cross-module.
- **Permission per-domain, singular, bukan ikut nama tabel**: `core.manage`,
  `student.manage`, `academic.manage`, `finance.manage` — bukan
  `core.manages`/`students.manage`/dst. Semua di-seed lewat `PermissionSeeder`
  (`database/seeders/`), **belum** ada CRUD Role & Permission lewat UI.
- **Staf = Blade (`guard: web`), Orang tua = API (`guard: sanctum`,
  read-only)** — Controller API orang tua SELALU di-scope dari
  `ParentProfile::where('user_id', ...)->with('students')` lalu cek
  `->students->contains('id', $targetId)` (pola dari `StudentApiController`,
  diikuti persis di `InvoiceApiController`) — **tidak pernah** query
  langsung dari sisi anak/data tanpa scope itu.
- **API Controller (parent-facing) pecah logic ke Action class**, Controller
  cuma manggil & format response — pola diambil dari `ParentAuthController`
  (modul Auth, paling matang), bukan query langsung di Controller.
- **Layout Blade staf**: `layouts.staff` (BUKAN `layouts.app`). Ada
  component `x-status-badge` (varian success/warning/danger, TIDAK ada
  varian netral/abu-abu — untuk status "tidak aktif" pakai `<span>` manual)
  dan `x-icon-badge`.
- **Padding input**: `class="... px-3.5 py-2.5 text-sm ..."` — pernah
  kelewat di awal (bug berulang), jangan lupa lagi.
- **JS per-halaman dipisah ke `resources/js/modules/{modul}/`**, auto-discover
  lewat glob di `vite.config.js`. Pola registrasi: `Alpine.data('namaFungsi',
  () => ({...}))` di dalam `document.addEventListener('alpine:init', ...)`.
  **PENTING**: file JS modul harus di-`@vite([...])` SEBELUM `app.js` dalam
  urutan dokumen (`layouts.staff` pakai `@stack('scripts')` yang taruhnya
  di ATAS `@vite(['resources/css/app.css', 'resources/js/app.js'])`) — kalau
  kebalik, `Alpine.start()` di `app.js` jalan duluan sebelum listener
  `alpine:init` modul sempat kedaftar, dan `x-data="xxx()"` gagal diam-diam
  tanpa error jelas. Ini kejadian nyata di modul Finance & makan waktu
  debug, jangan diulang di modul lain.
- **Bulk action (pilih banyak data sekaligus, mis. bulk assign/generate)**
  pakai pola 1 halaman: filter + checklist tampil bersamaan, live-update
  lewat AJAX `fetch()` ke endpoint `GET .../eligible-xxx`, BUKAN alur
  form→submit→halaman preview terpisah (sempat dicoba, diganti karena UX-nya
  kurang smooth). Lihat 2 contoh konkret di modul Finance: Bulk Mapping
  Tarif & Generate Invoice Massal.
- **Registrasi Provider ke `bootstrap/providers.php` selalu manual** —
  Claude tidak punya akses ke file itu, selalu diingatkan tiap modul baru.
- **Migration selalu dibuat manual oleh pemilik project** — Claude cuma
  jelaskan skemanya di chat (development stage, `migrate:fresh` aman).
- **Menghitung ulang status/nominal turunan (mis. status invoice) dari data
  transaksi asli**, bukan disimpan & di-update manual — supaya tidak ada
  celah tidak sinkron (lihat `RecalculateInvoiceStatusAction` di Finance).

---

## 📝 Yang Sengaja Belum Dibangun (bukan bug, cuma dicatat)

- CRUD Role & Permission lewat UI — masih manual lewat seeder/Tinker.
- Provider WhatsApp Gateway official belum dipilih. untuk sekarang testing masih pakai fonnte (unofficial).
  sudah pasang mekanisme WhatsApp gateway url dan wa gateway token dari fonnte di .env
- **Delegasi akses multi-akun orang tua** (ayah & ibu login terpisah, lihat
  anak yang sama) — konsep matang di `app/Modules/Auth/README.md`, domain
  Auth (bukan Student), belum dieksekusi.
- **Permission granular level guru/wali kelas** (input nilai per mapel) —
  dicatat di `app/Modules/Academic/README.md`, nunggu modul Teacher.
- Validasi kapasitas `classrooms.capacity` terhadap jumlah siswa di rombel
  — kolomnya ada, baru informasi, belum divalidasi keras.
- Nilai per mata pelajaran di Rapor — nunggu modul Exam/Learning.
- **Integrasi Finpay** (`PaymentGatewayTransaction`, `WebhookLogs`) —
  skema tabel sudah ada, provider belum final. Detail di
  `app/Modules/Finance/README.md`.
- **Download PDF invoice & endpoint "Bayar Sekarang"** — nunggu Finpay &
  fitur cetak PDF siap.
- **Halaman React sisi orang tua** — belum ada satupun, baru endpoint
  API (Auth, Student minimal, Finance/Invoice) yang siap dikonsumsi.
- **Birokrasi approval tarif khusus berjenjang** (Komite Tarif & Beasiswa
  — pengajuan dokumen, verifikasi, sidang pleno, SK) — sempat didiskusikan
  detail tapi diputuskan **belum** dibangun sebagai modul terpisah,
  `StudentTariffMapping` saat ini cukup pakai `approved_by`+`note` isi
  manual. Kalau nanti skalanya membesar, baru dipecah jadi modul sendiri.
- Test otomatis (Pest) untuk semua modul selain Auth.

---

## 🎨 Keputusan Desain yang Perlu Diikuti Konsisten

- **Views**: `resources/views/modules/{modul}/...` (dot notation standar),
  layout & component shared di LUAR folder `modules/` (`layouts/`,
  `components/`)
- **JS**: dipisah per halaman, mirror 1:1 struktur folder `views/modules/`,
  auto-discover lewat glob di `vite.config.js` (lihat aturan urutan `@vite`
  vs `app.js` di atas)
- **CSS**: satu `app.css` global (Tailwind utility-first), tidak per halaman
- **Gaya visual**: referensi Spike Admin (Wrappixel) — card rounded-2xl,
  soft shadow, icon badge pastel, warna semantik (hijau=baik, kuning=perlu
  perhatian, merah=masalah), Tabler Icons, font Plus Jakarta Sans
- **Struktur folder modul**: boleh nambah folder di luar standar minimum
  (`Middleware/`, `Notifications/`, `Jobs/`, dll.) selama logika bisnis tetap
  di `Actions/` — lihat catatan di `ARCHITECTURE.md`
- **Sidebar staf**: state grup accordion yang terbuka ditentukan dari
  `request()->routeIs([...])` di `x-data` (Blade), BUKAN disimpan lewat
  JS state — karena tiap navigasi = full page reload, bukan SPA. Animasi
  accordion pakai plugin `@alpinejs/collapse` (`x-collapse`, bukan
  `x-transition` bawaan yang cuma fade). Posisi scroll sidebar disimpan
  manual lewat `sessionStorage` (di luar Alpine) supaya tidak balik ke atas
  tiap pindah halaman.

---

## 💬 Preferensi Kerja dengan Claude (penting!)

- **Development, bukan production** — kalau ada perubahan skema tabel yang
  perlu, CUKUP JELASKAN skemanya di chat, JANGAN buat file migration baru
  sendiri — pemilik project yang bikin filenya & `migrate:fresh`.
- **Model files JANGAN dibuat/ditimpa Claude** — semua Model (termasuk
  relasi-relasinya) sudah dibuat manual oleh pemilik project duluan, bahkan
  untuk entitas yang Action/Controller-nya belum digarap (mis. seluruh
  Model Finance sudah ada dari awal project). Kalau sebuah Action butuh
  relasi tertentu, **jelaskan di chat relasi apa yang perlu ditambahkan**
  (kode method-nya), JANGAN kirim file Model lengkap.
- **Pengiriman file:** SELALU kirim di chat, satu per satu file dalam code
  block, lengkap path & status (baru/edit) — BUKAN zip. (Pola zip sekali di
  awal modul yang tadinya dipakai sudah tidak dipakai lagi mulai modul
  Finance, karena pemilik project sering modifikasi manual sendiri di
  file-file itu dan re-bundle zip bikin kerjaan dobel.)
  - **Sidebar/navigasi**: kalau cuma nambah 1-2 link baru, cukup kirim
    snippet `<a href="{{ route(...) }}">...</a>`-nya saja buat ditempel
    manual, jangan resend seluruh file sidebar.
- **Keputusan desain besar didiskusikan dulu** (pertanyaan singkat,
  bukan asumsi sepihak) — tapi begitu disepakati, eksekusi penuh tanpa
  ditunda. Termasuk: struktur skema tabel baru, penamaan permission,
  keputusan yang berdampak ke modul lain atau modul yang sudah stabil
  (terutama Auth), dan keputusan perhitungan/alokasi data yang ambigu
  (contoh: aturan FIFO vs proporsional di breakdown komponen biaya Finance).
- Pemilik project cukup teknis, terbuka didebat/dikoreksi kalau Claude
  salah asumsi (lihat riwayat: koreksi soal `ParentProfile` vs `parent_id`
  FK, penamaan permission, referensi file yang ternyata kurang relevan,
  dll) — Claude sebaiknya tetap teliti baca kode yang sudah ada sebelum
  menambahkan sesuatu di atasnya, bukan menebak. Kalau pemilik project
  ngasih file referensi buat ditiru gayanya, **pastikan dulu file itu
  relevan konteksnya** (mis. Controller staf vs Controller API itu beda
  konteks) sebelum ikut polanya mentah-mentah.