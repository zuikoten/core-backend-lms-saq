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
  dibangun (Auth, Student minimal, Finance/Invoice lengkap, Profil parent).
  Halaman React-nya sendiri **belum ada satupun** — itu pekerjaan terpisah
  yang belum dimulai.

---

## ✅ Status Modul

### Auth — stabil, sudah mencakup User/Role/Permission Management + Profil (Staf & Parent)
Login staf (email+password / OTP WA) & orang tua (OTP WA/password),
aktivasi akun orang tua by nomor HP, reset password, permission berbasis
Spatie. WhatsApp Gateway masih stub/testing pakai **Fonnte (unofficial)** —
URL & token gateway sudah dipasang di `.env`, provider official belum
dipilih. **Detail lengkap:** `app/Modules/Auth/README.md` — termasuk bagian
**"Rencana Masa Depan: Delegasi Akses Multi-Akun"** (konsep matang, belum
dieksekusi — supaya ayah & ibu bisa pantau anak dari akun masing-masing
tanpa gabung identitas). ⚠️ README modul ini belum di-update mengikuti
seluruh progress di bawah — perlu di-refresh sebelum sesi berikutnya baca
detail teknisnya.

**Fitur baru yang sudah dibangun (di luar yang sudah ada sebelumnya):**

- **User Management (staf)** — CRUD penuh (`/users`), permission
  `user.manage`. Multi-role per user (`syncRoles()`), search by nama/email/
  nomor HP (normalisasi format `0`↔`62` biar akurat), filter by role, list
  di-scope cuma role ber-`guard_name = 'web'` (parent/role guard lain
  otomatis gak nongol). Guard bisnis: gak bisa hapus akun sendiri, gak bisa
  melepas role `superadmin` dari akun terakhir yang megangnya.
- **Role & Permission Management** — CRUD Role (`/roles`), permission
  `role.manage`. Checkbox permission dikelompokkan otomatis per domain
  (`explode('.', $name)[0]` dari penamaan `{domain}.manage`/`{domain}.view`
  yang sudah baku). Role `superadmin` dikunci total (gak bisa diedit/
  dihapus/diganti nama lewat UI ini — tetap bypass semua permission lewat
  `Gate::before`, bukan dari isi `role_has_permissions`).
- **Kolom baru di `users`**: `name`, `username` (unique, buat rencana
  profil publik `/profil/{username}`, format slug lowercase+strip),
  `avatar` (path storage disk `public`, upload otomatis di-crop persegi +
  resize 300×300 PNG pakai GD, file lama dihapus tiap ganti).
- **Halaman "Profil Saya" — staf** (`/profile`, guard `web`): edit nama/
  username/avatar, ganti email, ganti nomor HP, ganti password — 3 yang
  terakhir wajib konfirmasi `current_password`, dengan rate limiter khusus
  `sensitive-profile-update` (10x/menit by `auth()->id()`, bukan reuse
  limiter `login` yang key-nya email/HP body request — gak relevan buat
  konteks yang sudah login).
- **Profil parent** (API, guard `sanctum`, `ParentProfileApiController`) —
  reuse Action/Request yang sama persis dengan staf buat nama/username/
  avatar/email/password (Action-nya emang gak peduli guard). **Ganti nomor
  HP beda pola**: wajib verifikasi OTP ke nomor **baru** dulu (bukan cuma
  modal `current_password`) — karena nomor HP itu kanal OTP login
  satu-satunya buat parent yang belum set password. Berhasil verifikasi →
  `users.phone_number` **dan** `parents.phone_number` di-update bareng
  dalam 1 `DB::transaction()` (update `parents` sengaja lewat `DB::table()`,
  BUKAN import Model — modul Auth itu fondasi, gak boleh import Model dari
  modul Student, lihat bagian "Keputusan Lintas Modul").
- **`otp_codes.action_type`** — nambah value baru `change_phone` (dari
  awalnya cuma `login`/`activation`/`reset_password`). Kolom `phone_number`
  di tabel ini sekarang diisi juga buat kasus `change_phone` (nomor baru
  yang mau diverifikasi), bukan cuma `activation` lagi.
- **`SetParentCredentialsAction` diperbaiki** — sekarang cuma bisa jalan
  kalau `email` DAN `password` user itu masih `NULL` (endpoint "lengkapi
  data pertama kali"), ditolak kalau salah satu sudah terisi (harus lewat
  jalur ganti email/password reguler yang minta konfirmasi password lama).
  Sebelumnya bisa overwrite kredensial diam-diam tanpa bukti kepemilikan.

**Bug & celah desain yang tercatat, status per sesi ini:**
- ~~Request OTP aktivasi parent tidak validasi keberadaan data `parents`
  dulu~~ — **sudah diperbaiki** (`Rule::exists('parents','phone_number')
  ->whereNull('user_id')` kondisional di `RequestOtpRequest`, khusus
  `action_type=activation`).
- ~~`ActivateParentAccountAction` pakai `assignRole('parent')` tanpa guard
  eksplisit~~ — **sudah diperbaiki**, sekarang eksplisit
  `assignRole(Role::findByName('parent', 'sanctum'))`. Sebelumnya
  "kebetulan benar" karena urutan resolusi guard Spatie
  (`Guard::getNames()`, ambil guard pertama yang provider-nya cocok ke
  `App\Models\User`), bukan by desain — rapuh kalau nanti ada guard baru
  yang provider-nya juga `users`.
- ~~`SetParentCredentialsAction` bisa overwrite kredensial tanpa bukti
  kepemilikan~~ — **sudah diperbaiki**, lihat poin di atas.
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
`phone_number` orang tua tidak bisa diubah lewat form staf biasa (itu
identitas login) — **satu-satunya jalur resmi ganti nomor HP parent sekarang
lewat "Profil Saya" milik parent sendiri + verifikasi OTP, lihat modul
Auth**. Staf: full CRUD (Blade). Orang tua: read-only, cuma lihat anak
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
✅ 5. Auth — User/Role/Permission Management (Blade) + Profil Saya (staf & parent)
👉 6. Finance — Proses pembayaran + integrasi payment Gateway Finpay (SELANJUTNYA)
```

**Yang bisa jadi agenda selanjutnya** (belum ada urutan resmi disepakati,
diskusikan dulu sama pemilik project sebelum eksekusi):
- **Integrasi Finpay** — begitu provider final, tinggal isi
  `PaymentGatewayTransaction` & `WebhookLogs` di modul Finance yang sudah
  ada kerangkanya.
- **Halaman React sisi orang tua** — endpoint API Auth, Student
  (minimal), Finance/Invoice, dan Profil parent sudah siap dikonsumsi,
  pembuatan halaman React dibangun terpisah oleh tim lain dan sedang dalam
  progress.
- **Teacher** — sengaja terus dilewati sejauh ini. `class_groups.homeroom_teacher_id`
  sudah disiapkan nullable (kolom polos, belum FK constraint) supaya bisa
  diisi belakangan tanpa migrasi ulang.
- **Halaman profil publik `/profil/{username}`** — kolom `username` sudah
  ada & bisa diisi (lewat "Profil Saya"), tapi halaman yang benar-benar
  menampilkannya berdasarkan slug ini **belum dibangun** — tujuan awal
  kolom ini (buat URL profil publik, berlaku lintas tipe user: staf,
  parent, nanti student) belum tercapai sepenuhnya sampai halaman ini ada.
- **Manajemen sesi/perangkat aktif** — terutama relevan buat parent:
  `ParentAuthController::login()` bikin token Sanctum baru tiap login
  (`createToken('parent-app')`), jadi 1 akun bisa punya banyak token aktif
  bersamaan (multi-device) tanpa ada cara lihat/cabut salah satunya dari
  sisi user. Makin relevan begitu "Delegasi Akses Multi-Akun" (lihat README
  Auth) dieksekusi.
- **Notifikasi keamanan saat data sensitif berubah** — kirim WA/email ke
  kontak **lama** (bukan yang baru) begitu email/nomor HP/password diganti
  lewat "Profil Saya" (staf maupun parent), supaya kalau perubahan itu
  gak sah, pemilik asli akun langsung tau. Scope-nya lebih luas dari catatan
  lama "notifikasi kalau password direset" — sekarang ada 3 kanal sensitif
  (email, HP, password), bukan cuma password.
- **Audit `last_login_at` / `last_login_ip`** — kolom baru di `users`,
  diupdate tiap login sukses (staf & parent, semua jalur: email+password,
  OTP WA). Berguna buat admin (lihat kapan staf terakhir aktif di halaman
  "Pengguna") maupun parent sendiri (info dasar keamanan).
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
- **Arah dependency SATU ARAH**: modul yang lebih "fondasi" (Core, **juga
  Auth**) **tidak pernah** import Model dari modul yang mengonsumsinya
  (Student, Academic, Finance). Guard/Action yang perlu tahu atau ubah data
  di modul lain, query lewat `DB::table()` by nama tabel, bukan import
  Model cross-module. Contoh konkret terbaru: `ConfirmParentPhoneChangeAction`
  (modul Auth) update tabel `parents` (milik modul Student) lewat
  `DB::table('parents')`, bukan import `ParentProfile`.
- **Spatie Permission — guard SELALU eksplisit, jangan andalkan resolusi
  implisit.** `assignRole('nama')`/`Role::findByName('nama')` tanpa guard
  eksplisit resolve guard dari `Guard::getNames()` (ambil guard pertama di
  `config/auth.php` yang provider-nya cocok ke model User) — bisa
  "kebetulan benar" sekarang tapi rapuh kalau config berubah. Selalu tulis
  `assignRole(Role::findByName('nama', 'web'|'sanctum'))` eksplisit. Kasus
  nyata: `ActivateParentAccountAction` (lihat status Auth di atas).
- **Permission per-domain, singular, bukan ikut nama tabel**: `core.manage`,
  `student.manage`, `academic.manage`, `finance.manage`, `user.manage`,
  `role.manage` — bukan `core.manages`/`students.manage`/dst. Semua
  di-seed lewat `PermissionSeeder` (`database/seeders/`). **CRUD Role &
  Permission sekarang sudah ada lewat UI** (modul Auth, `/roles`, `/users`)
  — role `superadmin` dikunci gak bisa diubah/dihapus lewat UI itu.
- **Data sensitif (email/nomor HP/password) yang bisa diubah user sendiri
  via "Profil Saya" wajib lapisan konfirmasi ekstra** — pola standar:
  minta `current_password` (rule bawaan Laravel `'current_password'`) buat
  channel yang **staf** kontrol penuh (email, password). **Kecuali** kalau
  channel itu sendiri yang jadi satu-satunya bukti kepemilikan akun (kasus
  nomor HP milik **parent**, karena itu kanal OTP login) — di situ wajib
  verifikasi OTP ke nilai **baru** dulu, bukan cuma modal password lama.
  Route yang minta `current_password` dikasih rate limiter khusus
  `sensitive-profile-update` (beda dari limiter `login`, karena konteksnya
  sudah-login, key-nya `auth()->id()` bukan email/HP body request).
- **Normalisasi nomor HP terpusat** — logic (buang non-digit, ganti awalan
  `0`→`62`) ada di 2 tempat yang **beda tujuan, dua-duanya tetap perlu**:
  mutator `phoneNumber()` di `App\Models\User` (urus format yang
  tersimpan), dan trait `Modules\Auth\Requests\Concerns\NormalizesPhoneNumber`
  dipakai di tiap Form Request yang terima input nomor HP (urus supaya
  rule `unique`/`exists` membandingkan nilai dalam format yang sama dengan
  yang ada di database, SEBELUM sempat disimpan lewat mutator).
- **API Controller (parent-facing) pecah logic ke Action class**, Controller
  cuma manggil & format response — pola diambil dari `ParentAuthController`
  (modul Auth, paling matang), bukan query langsung di Controller.
- **Layout Blade staf**: `layouts.staff` (BUKAN `layouts.app`). Ada
  component `x-status-badge` (varian success/warning/danger, TIDAK ada
  varian netral/abu-abu — untuk status "tidak aktif" pakai `<span>` manual)
  dan `x-icon-badge`.
- **Padding input**: `class="... px-3.5 py-2.5 text-sm ..."` — pernah
  kelewat di awal (bug berulang), jangan lupa lagi. **Input password**
  beda pola (lihat `login.blade.php`/`profile/edit.blade.php`): `px-4
  py-2.5 pr-11`, `focus:ring-2 focus:ring-{color}-500 focus:border-{color}-500`,
  wajib toggle show/hide (`x-data="{ show: false }"`, ikon
  `ti-eye`/`ti-eye-off`, 1 `x-data` terpisah per field biar independen).
- **Accordion sidebar yang baru dibuka scroll otomatis ke posisi terlihat**
  — method `toggleGroup(name)` di `x-data` root `<aside>`, pakai
  `$nextTick` + `setTimeout` (nunggu animasi `x-collapse` ~250ms selesai)
  sebelum `scrollIntoView({ block: 'end' })` ke `x-ref="{name}Panel"`.
  Nama key grup dipakai sebagai nama `$refs`, jadi harus camelCase tanpa
  strip (`dataMaster`, bukan `data-master`).
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

- Provider WhatsApp Gateway official belum dipilih. Untuk sekarang testing
  masih pakai Fonnte (unofficial). Sudah pasang mekanisme WhatsApp gateway
  URL dan WA gateway token dari Fonnte di `.env`.
- **Delegasi akses multi-akun orang tua** (ayah & ibu login terpisah, lihat
  anak yang sama) — konsep matang di `app/Modules/Auth/README.md`, domain
  Auth (bukan Student), belum dieksekusi.
- **Halaman profil publik `/profil/{username}`** — kolom `username` sudah
  ada, halaman penampilnya belum dibangun. Lihat "Agenda Selanjutnya".
- **Manajemen sesi/perangkat aktif (multi-device Sanctum)** — belum ada
  cara user lihat/cabut token aktif miliknya sendiri. Lihat "Agenda
  Selanjutnya".
- **Notifikasi keamanan** saat email/nomor HP/password diganti lewat
  "Profil Saya" — belum ada, baik buat staf maupun parent. Lihat "Agenda
  Selanjutnya".
- **Audit `last_login_at`/`last_login_ip`** — kolomnya belum ada di
  `users`. Lihat "Agenda Selanjutnya".
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
  API (Auth, Student minimal, Finance/Invoice, Profil parent) yang siap
  dikonsumsi.
- **Birokrasi approval tarif khusus berjenjang** (Komite Tarif & Beasiswa
  — pengajuan dokumen, verifikasi, sidang pleno, SK) — sempat didiskusikan
  detail tapi diputuskan **belum** dibangun sebagai modul terpisah,
  `StudentTariffMapping` saat ini cukup pakai `approved_by`+`note` isi
  manual. Kalau nanti skalanya membesar, baru dipecah jadi modul sendiri.
- Test otomatis (Pest) untuk semua modul selain Auth — dan test Auth yang
  ada pun belum diperluas mengikuti fitur User/Role/Profil yang baru.

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
  tiap pindah halaman. Grup yang baru dibuka auto-scroll ke posisi
  terlihat (lihat `toggleGroup()` di "Keputusan Lintas Modul").

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
- **Pengiriman file — tergantung konteks:**
  - **Modul/fitur baru, banyak file baru sekaligus** → bundling **ZIP**
    lengkap sesuai struktur direktori, lebih gampang dipindah dibanding
    tempel manual satu-satu (pola dipakai lagi mulai fitur User/Role/
    Permission Management, setelah sempat ditinggalkan pas modul Finance).
  - **Edit ke file yang SUDAH ADA** (Controller/Action/Request/View lama
    yang cuma nambah/ubah beberapa baris) → tetap kirim di chat, snippet
    kode dalam code block, jelas bagian mana yang diganti — BUKAN
    zip ulang seluruh file, supaya gak menimpa modifikasi manual pemilik
    project yang belum sempat di-sync ke Claude.
  - **Sidebar/navigasi**: kalau cuma nambah 1-2 link baru, cukup kirim
    snippet `<a href="{{ route(...) }}">...</a>`-nya saja buat ditempel
    manual, jangan resend seluruh file sidebar.
- **Keputusan desain besar didiskusikan dulu** (pertanyaan singkat,
  bukan asumsi sepihak) — tapi begitu disepakati, eksekusi penuh tanpa
  ditunda. Termasuk: struktur skema tabel baru, penamaan permission,
  keputusan yang berdampak ke modul lain atau modul yang sudah stabil
  (terutama Auth), dan keputusan perhitungan/alokasi data yang ambigu
  (contoh: aturan FIFO vs proporsional di breakdown komponen biaya Finance,
  atau pola verifikasi OTP vs current_password buat ganti nomor HP parent).
- Pemilik project cukup teknis, terbuka didebat/dikoreksi kalau Claude
  salah asumsi (lihat riwayat: koreksi soal `ParentProfile` vs `parent_id`
  FK, penamaan permission, referensi file yang ternyata kurang relevan,
  resolusi guard Spatie yang ternyata bukan "default ke web" seperti
  dugaan awal Claude, dll) — Claude sebaiknya tetap teliti baca kode yang
  sudah ada sebelum menambahkan sesuatu di atasnya, bukan menebak. Kalau
  pemilik project ngasih file referensi buat ditiru gayanya, **pastikan
  dulu file itu relevan konteksnya** (mis. Controller staf vs Controller
  API itu beda konteks) sebelum ikut polanya mentah-mentah.
