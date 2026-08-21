# 🔐 Modul Auth

Modul ini menangani seluruh urusan autentikasi & otorisasi sistem: login, aktivasi akun, reset password, pengaturan akses (role & permission), manajemen akun staf, dan profil self-service (staf maupun orang tua). Dokumen ini menjelaskan **apa isinya dan kenapa dibangun begitu** — untuk detail arsitektur umum lintas modul, lihat `ARCHITECTURE.md` di root project.

---

## Dua Jenis Pengguna, Dua Mekanisme Login Berbeda

| | Staf sekolah | Orang tua/wali |
|---|---|---|
| Contoh role | superadmin, kepala_sekolah, staff_admin, guru | parent |
| Guard | `web` (sesi Blade) | `sanctum` (token, dikonsumsi React) |
| Cara masuk | Email+password **atau** OTP WhatsApp | OTP WhatsApp **atau** password |
| Dibuat lewat | Halaman "Pengguna" (`/users`, staf ber-permission `user.manage`) — **tidak ada self-register** | Aktivasi mandiri, harus cocok dengan data `parents` yang sudah diinput staf lebih dulu |

Kenapa dipisah total (bukan 1 sistem generik)? Karena kebutuhan aksesnya beda jauh: staf butuh panel Blade dengan banyak halaman, parent cuma butuh API buat dikonsumsi aplikasi React terpisah. Memaksakan 1 mekanisme buat keduanya bakal bikin salah satu sisi jadi kompromi.

---

## Alur Staf (Blade, guard `web`)

### Login
- **Email + password** — `AuthController::login()` → `AuthenticateStaffAction`. Ada rate limit percobaan gagal beruntun (5x/menit per email).
- **OTP WhatsApp** — toggle tab di halaman login yang sama (`login.blade.php`). Alurnya: masukkan nomor HP → `AuthController::requestLoginOtp()` (`RequestStaffLoginOtpAction`) → kode dikirim → halaman verifikasi 6-kotak (`login-otp-verify.blade.php`) → `AuthController::loginWithOtp()` (`AuthenticateStaffWithOtpAction`) → langsung masuk dashboard.

### Lupa Password
Ada di `/forgot-password`, halaman pilihan dulu (`StaffPasswordResetController::showChooseForm`), lalu cabang ke 2 jalur:

- **Email** — broker password bawaan Laravel (`Password::broker()`). Butuh tabel `password_reset_tokens` & `MAIL_MAILER` terkonfigurasi (development cukup `log` atau Mailpit).
- **OTP WhatsApp** — dipecah **3 halaman terpisah** (bukan 1 halaman gabungan), supaya verifikasi OTP dan set password baru jadi 2 keputusan yang jelas:
  1. Minta OTP (input nomor HP) — `requestOtp()` / `RequestStaffPasswordResetOtpAction`
  2. Verifikasi OTP (6-kotak) — `verifyOtp()`, pakai `VerifyOtpAction` langsung
  3. Set password baru — `setNewPassword()` / `SetPasswordAfterOtpVerificationAction`

  Step 2→3 dijaga pakai **session flag** (`staff_reset_otp_verified_phone`) — begitu OTP tervalidasi, nomor itu "ditandai lolos" di session selama proses berlangsung; halaman step 3 menolak diakses langsung tanpa lewat step 2 dulu.

### Kenapa OTP dipisah dari password baru, alih-alih 1 form gabungan?
Supaya user dapat kepastian instan begitu kode-nya benar (langsung pindah halaman), bukan baru tahu kodenya salah setelah capek-capek isi password juga. Konsekuensinya butuh "tiket" antar-step (session flag) supaya orang tidak bisa loncat ke step set-password tanpa verifikasi.

---

## User, Role & Permission Management (Blade, staf)

Menu **"Pengguna"** & **"Role & Hak Akses"** di sidebar (grup "Pengaturan"), buat kelola akun staf sepenuhnya lewat UI — sebelumnya ini semua manual lewat seeder/Tinker.

### User Management (`/users`, permission `user.manage`)
- CRUD penuh: `UserController` → `CreateUserAction` / `UpdateUserAction` / `DeleteUserAction`.
- **Multi-role per user** — 1 akun staf boleh punya lebih dari 1 role sekaligus, disimpan lewat `syncRoles()` (bukan `assignRole()` tunggal).
- **List di-scope cuma role ber-`guard_name = 'web'`** (`ListStaffUsersAction`) — akun parent (`guard_name = 'sanctum'`) sengaja gak pernah nongol di halaman ini, walau sama-sama baris di tabel `users`. Query pakai `whereHas('roles', fn ($q) => $q->where('guard_name', 'web'))`, bukan blacklist nama role satu-satu — otomatis ikut nge-exclude role tipe user lain (mis. `student`) kalau nanti dibangun dengan pola guard terpisah serupa parent.
- **Search** by nama/email/nomor HP, nomor HP dinormalisasi dulu (format `0`↔`62`) sebelum di-`LIKE`, supaya user bisa cari pakai format lokal yang wajar diketik.
- **Filter** by role (dropdown, isinya cuma role ber-guard `web`).
- **Guard bisnis** (`UpdateUserAction`/`DeleteUserAction`):
  - Gak bisa hapus akun sendiri.
  - Gak bisa melepas role `superadmin` dari akun kalau itu satu-satunya yang masih pegang role itu (cegah lockout total sistem).

### Role & Permission Management (`/roles`, permission `role.manage`)
- CRUD Role: `RoleController` → `CreateRoleAction` / `UpdateRoleAction` / `DeleteRoleAction`.
- Checkbox **Permission dikelompokkan otomatis per domain** — `explode('.', $permission->name)[0]` dari penamaan baku `{domain}.manage`/`{domain}.view` (bukan hardcode daftar grup manual).
- **Daftar Permission itu sendiri FIXED, di-seed dari kode** (`PermissionSeeder`), **BUKAN** bisa dibuat bebas lewat UI ini — form Role cuma nyentang permission yang sudah ada, gak bisa nambah opsi baru. Alasannya: Permission cuma berarti kalau ada middleware/kode yang benar-benar ngeceknya (`permission:{domain}.manage`); Permission buatan bebas lewat UI cuma jadi baris kosong yang gak nyambung ke logic apa pun.
- **Role `superadmin` dikunci total lewat UI ini** — gak bisa diedit nama/permission-nya, gak bisa dihapus (`UpdateRoleAction`/`DeleteRoleAction` tolak eksplisit by nama). `superadmin` tetap bypass semua permission lewat `Gate::before` terlepas dari isi `role_has_permissions`-nya — dikunci di UI supaya gak menyesatkan (seolah bisa diatur padahal gak berefek ke akses nyata).
- Role gak bisa dihapus kalau masih ada user yang pegang (`$role->users()->count() > 0`).

---

## Profil Saya — Self-Service (Blade, staf)

Menu di navbar (avatar pojok kanan atas, link ke `/profile`) & card bawah sidebar — **beda dari User Management di atas**: ini staf ngedit **data dirinya sendiri**, bukan admin ngedit staf lain. Route-nya di grup `panel.access` (semua staf yang login boleh akses, bukan cuma yang punya `user.manage`).

`ProfileController` (`Modules\Auth\Controllers`) punya 4 aksi terpisah, masing-masing Action & Request sendiri:

| Aksi | Field | Konfirmasi | Action |
|---|---|---|---|
| Info Profil | `name`, `username`, `avatar` | — | `UpdateProfileAction` |
| Ganti Email | `email` | wajib `current_password` | `UpdateEmailAction` |
| Ganti Nomor HP | `phone_number` | wajib `current_password` | `UpdatePhoneAction` |
| Ganti Password | `password` | wajib `current_password` (password lama) | `UpdatePasswordAction` |

- **`name`/`username`/`avatar`** — kolom baru di `users`, dianggap atribut universal semua tipe user (staf, parent, nanti student), bukan cuma staf:
  - `username` — opsional, unique, format slug lowercase+strip (`^[a-z0-9]+(-[a-z0-9]+)*$`), tujuannya buat jadi URL profil publik (`/profil/{username}` — **halaman penampilnya belum dibangun**, lihat "Yang Sengaja Belum Dibangun").
  - `avatar` — upload apa pun (format/ukuran), otomatis **di-crop persegi dari tengah lalu di-resize 300×300 PNG** pakai GD (`UpdateProfileAction::storeCompressedAvatar()`), disimpan di disk `public` folder `avatars`. File lama otomatis dihapus tiap ganti foto, supaya storage gak numpuk file yatim. Nama file pakai `uniqid()` supaya gak ke-cache browser kalau ganti-ganti foto.
- **3 aksi lain (email/HP/password) wajib konfirmasi `current_password`** (rule bawaan Laravel `'current_password'`) — karena email & nomor HP dipakai jalur reset password/OTP, ganti tanpa konfirmasi itu celah keamanan (sesi ke-hijack bisa dipakai buat ambil alih akun total).
- **Rate limiter khusus `sensitive-profile-update`** (`AuthRateLimiterServiceProvider`, 10x/menit by `auth()->id()`) dipasang di 3 route yang minta `current_password` — **sengaja bukan reuse limiter `login`**, karena `login` di-key dari `email`/`phone_number` di body request (yang gak selalu ada, mis. route ganti password gak punya field itu sama sekali) dan konteksnya beda (sebelum vs sesudah login, identitas akun sudah pasti dari `auth()->id()` bukan ditebak dari body).
- **Normalisasi nomor HP** pakai trait `Modules\Auth\Requests\Concerns\NormalizesPhoneNumber`, dipakai bareng oleh `StoreUserRequest`, `UpdateUserRequest`, `UpdatePhoneRequest` — lihat bagian "Standar Nomor HP" soal kenapa ini tetap perlu walau mutator Model sudah ada.

---

## Profil Parent (API, guard `sanctum`)

`ParentProfileApiController`, route di luar prefix `auth` (`/api/profile/*`, bukan `/api/auth/profile/*`) — semantiknya beda: prefix `auth` khusus proses otentikasi (OTP/login/logout), sedangkan `/profile` itu kelola data akun yang sudah terbukti login.

- **`name`/`username`/`avatar`/`email`/`password`** — **reuse langsung** `UpdateProfileAction`, `UpdateEmailAction`, `UpdatePasswordAction` (dan Request-nya) yang sama persis dengan punya staf. Action-Action ini gak peduli guard sama sekali (cuma kerja ke `User $user`), jadi aman dipakai ulang tanpa modifikasi.
- **Ganti nomor HP beda pola total dari staf** — bukan cukup `current_password`, tapi **wajib verifikasi OTP ke nomor baru dulu**:
  1. `POST /api/profile/phone/otp/request` (`RequestParentPhoneChangeOtpAction`) — kirim OTP (`action_type = change_phone`) ke nomor **baru** yang diinput. Ditolak kalau nomor baru sama dengan yang sekarang, atau sudah dipakai user lain.
  2. `POST /api/profile/phone/otp/confirm` (`ConfirmParentPhoneChangeAction`) — verifikasi kode lewat `VerifyOtpAction` (branch `else`, dicari `by user_id`, sama seperti `login`/`reset_password`). Nomor yang benar-benar disimpan diambil dari **kolom `phone_number` milik baris OTP yang berhasil diverifikasi**, bukan input ulang di step ini — supaya gak ada celah kirim nomor beda antara step request & confirm.
  3. Berhasil → `users.phone_number` **dan** `parents.phone_number` di-update bareng dalam 1 `DB::transaction()`. Update ke `parents` sengaja lewat `DB::table('parents')`, **bukan** import Model `ParentProfile` dari modul Student — modul Auth adalah modul fondasi, gak boleh import Model dari modul konsumen (lihat `STYLE_GUIDE.md` bagian 2).
  4. Kenapa nomor HP parent boleh disinkronkan (beda dari aturan staf yang gak boleh sembarangan edit `parents.phone_number`)? Karena larangan itu spesifik buat melindungi **matching pra-aktivasi** (`parents.user_id` masih `NULL`). Begitu sudah diklaim (`user_id` terisi), parent ganti nomor kontak dirinya sendiri itu operasi normal — dan **wajib** disinkronkan, karena kalau tidak, data yang staf lihat di modul Student jadi basi (nomor gak valid dihubungi).
- **`setCredentials`** (`ParentAuthController`, bukan `ParentProfileApiController` — tetap di controller lama karena masih bagian dari alur aktivasi/lengkapi akun) — buat parent OTP-only (belum punya email/password) isi kredensial pertama kali. **Diperbaiki**: sekarang cuma bisa jalan kalau `email` **dan** `password` user itu masih `NULL` (`SetParentCredentialsAction`) — ditolak kalau salah satu sudah terisi, harus lewat jalur "Ganti Email"/"Ganti Password" di atas yang minta konfirmasi. Sebelumnya endpoint ini bisa overwrite kredensial existing tanpa bukti kepemilikan (celah keamanan kalau sesi ke-hijack).

---

## Alur Orang Tua/Wali — Otentikasi (API, guard `sanctum`)

Semua lewat `ParentAuthController`, prefix `/api/auth`:

- `POST /api/auth/otp/request` — minta OTP (activation/login/reset_password, dibedakan lewat `action_type`)
- `POST /api/auth/otp/verify` — verifikasi OTP, hasilnya beda tergantung `action_type`:
  - `activation` → `ActivateParentAccountAction` (cocokkan nomor HP ke `parents.phone_number` yang sudah diinput staf, buat akun baru, assign role `parent` **eksplisit dengan guard** — lihat catatan di bawah)
  - `login` → `AuthenticateParentWithOtpAction` (buat token Sanctum)
  - `reset_password` → `ResetPasswordWithOtpAction` (verifikasi + ganti password **1 request**, beda dari staf yang dipecah 2 step — di sini boleh digabung karena React yang atur UX-nya sendiri, tidak perlu reload halaman)
- `POST /api/auth/login` — login pakai nomor HP + password (opsional, buat kondisi tanpa sinyal/pulsa)
- `POST /api/auth/logout` — revoke token aktif
- `POST /api/auth/credentials` — lengkapi email+password pertama kali, lihat `setCredentials` di bagian "Profil Parent" di atas.

**Kenapa parent tidak bisa self-register bebas?** Karena data siswa & orang tua sudah diinput staf sekolah lebih dulu (baris `parents` dengan `user_id = NULL`). Aktivasi cuma mencocokkan nomor HP ke baris yang sudah ada — mencegah orang asing bikin akun mengatasnamakan siswa yang tidak terdaftar.

**Catatan perbaikan**: `ActivateParentAccountAction` sekarang pakai `assignRole(Role::findByName('parent', 'sanctum'))` eksplisit — sebelumnya `assignRole('parent')` tanpa guard, yang "kebetulan benar" karena urutan resolusi guard Spatie (`Guard::getNames()`, ambil guard pertama di `config/auth.php` yang provider-nya cocok ke model `User`), bukan by desain. Ada 2 baris Role bernama `parent` di database (`id 5` guard `web`, `id 6` guard `sanctum`) — baris `web` itu peninggalan sebelum bug ini diperbaiki, harus dicek manual jangan sampai ada user yang nyangkut ke situ.

---

## OTP: Satu Mekanisme, Dipakai di Banyak Tempat

Semua OTP (aktivasi parent, login staf, login parent, reset password staf, reset password parent, **ganti nomor HP parent**) lewat 2 Action yang sama:

- **`GenerateOtpAction`** — generate 6 digit, simpan ke `otp_codes`, rate limit 60 detik antar-request per nomor/user, expire 5 menit. `action_type` yang valid: `'activation' | 'login' | 'reset_password' | 'change_phone'`.
- **`VerifyOtpAction`** — cek `is_used`, `expires_at`, cocokkan kode. **Membatasi percobaan salah** (`attempts`, default maksimal 3x per baris OTP) — begitu kelewat batas, baris itu langsung dipaksa `is_used = true` walau belum expired, supaya tidak bisa terus ditebak.

`action_type` menentukan query-nya: `activation` dicari lewat `phone_number` (belum tentu ada user); `login`/`reset_password`/**`change_phone`** dicari lewat `user_id`. Kolom `otp_codes.phone_number` sekarang diisi buat 2 kasus: `activation` (nomor pendaftar) dan `change_phone` (nomor **baru** yang mau diverifikasi) — bukan cuma `activation` lagi seperti sebelumnya.

**Gateway WhatsApp masih stub buat testing** (`LogWhatsappGateway` sebagai fallback; testing aktif sekarang pakai **Fonnte, unofficial** — URL & token gateway sudah dipasang di `.env`). Dibungkus lewat `WhatsappGatewayInterface` supaya provider final (resmi) tinggal ganti 1 baris binding di `AuthModuleServiceProvider`, tanpa ubah kode manapun yang memanggilnya.

---

## Akses & Permission

Sistem akses berbasis **permission**, bukan nama role yang di-hardcode — supaya nambah role baru (guru, kepala sekolah, bendahara) tidak perlu ubah kode:

- `superadmin` **bypass total** semua `can()`/`@can`, lewat `Gate::before` di `AuthModuleServiceProvider::registerGates()`. Tidak perlu di-assign permission apa pun, termasuk yang belum dibuat sekalipun. Role `superadmin` juga dikunci gak bisa diedit/dihapus lewat UI Role Management (lihat bagian di atas).
- Role lain butuh permission eksplisit. Permission minimum yang dipakai modul ini: **`panel.access`** — syarat untuk bisa login & masuk panel staf sama sekali (dicek di middleware route `staff.*` dan di semua Action terkait staf: `AuthenticateStaffAction`, `RequestStaffLoginOtpAction`, `AuthenticateStaffWithOtpAction`, `RequestStaffPasswordResetOtpAction`, `ResetPasswordWithOtpAction`). Route "Profil Saya" (`/profile/*`) juga ada di grup `panel.access` ini — semua staf yang login boleh akses, bukan permission terpisah.
- **`user.manage`** & **`role.manage`** — permission buat halaman "Pengguna" & "Role & Hak Akses" (lihat bagian "User, Role & Permission Management" di atas). Ini permission pertama di luar `panel.access` yang lahir dari modul Auth sendiri (sebelumnya cuma `{domain}.manage` dari modul lain).
- Permission granular per fitur lain (`finance.manage`, `academic.view`, dst.) di-seed oleh modul masing-masing lewat `PermissionSeeder`.

---

## Middleware Modul Ini

- **`EnsureUserIsActive`** — cek `users.is_active` di setiap request terautentikasi (web maupun sanctum), auto-logout/reject kalau `false` (mis. staf dinonaktifkan di tengah sesi aktif).

## Rate Limiting

Didaftarkan di `AuthRateLimiterServiceProvider` (terpisah dari `AuthModuleServiceProvider`, biar tidak numpuk):
- `otp-request` — maks 3x/menit per nomor HP, cegah spam yang membebani biaya WA Gateway
- `login` — maks 10x/menit per email/nomor HP, cegah brute force login (dipakai juga oleh verifikasi OTP & konfirmasi ganti nomor HP parent — konteksnya sama-sama "tebak kode/kredensial sebelum identitas terverifikasi")
- **`sensitive-profile-update`** — maks 10x/menit **by `auth()->id()`** (bukan email/HP dari body request), cegah brute force tebak `current_password` di form Ganti Email/HP/Password milik "Profil Saya" staf. Lihat bagian "Profil Saya" soal kenapa ini limiter terpisah, bukan reuse `login`.

## Standar Nomor HP

Semua nomor HP disimpan dalam format **`62xxxxxxxxxx`** (tanpa `0`/`+` di depan). Ada **2 lapisan normalisasi, dua-duanya tetap perlu jalan bareng** (bukan salah satu boleh dihapus):

1. **Mutator `phoneNumber()` di `App\Models\User`** — jalan di titik manapun nilai itu di-assign (form, seeder, Tinker, import), urus **format yang tersimpan** di database.
2. **Trait `Modules\Auth\Requests\Concerns\NormalizesPhoneNumber`** — dipakai di `prepareForValidation()` tiap Form Request yang terima input nomor HP (`StoreUserRequest`, `UpdateUserRequest`, `UpdatePhoneRequest`, `RequestPhoneChangeOtpRequest`). Ini urus supaya rule `unique`/`exists` **membandingkan nilai dalam format yang sama** dengan yang ada di database — soalnya rule itu jalan **sebelum** mutator sempat menormalisasi nilai yang baru diinput. Tanpa ini, orang isi `08985...` padahal `6285...`-nya sendiri sudah kepakai user lain bisa **lolos validasi unique** karena dibandingkan sebagai string yang beda.

---

## Struktur Folder (khusus tambahan di luar standar `ARCHITECTURE.md`)

```text
app/Modules/Auth/
├── Middleware/       EnsureUserIsActive
├── Notifications/    SendOtpWhatsappNotification, Channels/ (WhatsappChannel, LogWhatsappGateway), Contracts/ (WhatsappGatewayInterface)
├── Providers/        AuthModuleServiceProvider (routes, Gate::before, WA binding), AuthRateLimiterServiceProvider
├── Requests/Concerns/  NormalizesPhoneNumber (trait, dipakai bareng Request yang terima input nomor HP)
├── web.php           Route Blade staf (User/Role/Profil termasuk di sini, guard 'web')
└── api.php           Route API parent (Profil parent termasuk di sini, guard 'sanctum', di luar prefix 'auth')
```

## Yang Sengaja Belum Dibangun

- **Halaman profil publik `/profil/{username}`** — kolom `username` sudah ada & bisa diisi lewat "Profil Saya", tapi halaman penampilnya berdasarkan slug ini belum dibangun.
- **Manajemen sesi/perangkat aktif** — parent (dan nanti staf) belum bisa lihat/cabut token Sanctum aktif miliknya sendiri; tiap login bikin token baru tanpa batas.
- **Notifikasi keamanan** — belum ada notifikasi ke kontak lama begitu email/nomor HP/password diganti lewat "Profil Saya" (staf maupun parent).
- **Audit `last_login_at`/`last_login_ip`** — kolomnya belum ada di `users`.
- Notifikasi ke user asli kalau password mereka direset via jalur "Lupa Password" (beda dari ganti password self-service di atas yang sudah minta konfirmasi `current_password`).
- Test otomatis untuk fitur User/Role/Permission/Profil yang baru dibangun — test lama (7 test) belum mencakup ini.

---

## 🔮 Rencana Masa Depan: Delegasi Akses Multi-Akun untuk Orang Tua

**Masalah yang mau diselesaikan:** sekarang 1 keluarga (`parents`) cuma bisa punya 1 nomor HP/akun. Padahal kadang ayah dan ibu (atau wali lain) sama-sama mau pantau progress anak dari HP masing-masing.

**Pendekatan yang dipilih: delegasi akses, bukan gabung identitas.** Sempat dipertimbangkan alternatif "1 keluarga boleh banyak nomor HP" (`parent_phone_numbers`), tapi itu berarti bongkar `ActivateParentAccountAction` dan seluruh alur aktivasi yang sudah stabil sekarang. Pendekatan yang dipilih justru **tidak mengubah apa pun yang sudah ada** — akun utama (yang aktivasi lewat nomor HP di `parents.phone_number`, seperti sekarang) tetap seperti biasa. Yang baru cuma lapisan akses tambahan di atasnya, mirip pola "beri wewenang staf toko" di aplikasi e-commerce: 1 data (toko/keluarga), banyak akun dengan level akses berbeda.

### Tabel baru
```text
parent_access_grants
├── id PK
├── parent_id FK → parents.id (data keluarga yang diakses)
├── user_id FK → users.id, unique (akun yang diberi akses)
├── granted_by FK nullable → users.id (akun utama yang meng-invite, buat audit)
└── timestamps
```

`parents` dan alur aktivasi akun utama **tidak berubah sama sekali**. Endpoint read-only milik parent (mis. `StudentApiController::index`) tinggal ditambah 1 kondisi: selain `parents.user_id = auth()->id()`, juga cek apakah ada baris di `parent_access_grants` untuk `parent_id` itu.

### Alur: self-service, lewat OTP invite baru
- **Cuma akun utama** yang boleh mengundang — bukan staf, bukan orang tua kedua yang belum diverifikasi.
- Akun utama login di React → fitur "Undang orang tua/wali lain" → masukin nomor HP → OTP dikirim ke nomor itu (action_type baru, mis. `invite_access`, pakai `GenerateOtpAction`/`VerifyOtpAction` yang sudah ada, tidak perlu OTP generik baru) → begitu diverifikasi oleh pemilik nomor itu, baris baru masuk ke `parent_access_grants`, otomatis dapat akses.
- **Pencabutan akses**: hanya akun utama yang boleh mencabut (konsisten dengan siapa yang mengundang), lewat React, bukan panel staf.

### Wewenang: read-only dulu
Di tahap awal, akun hasil delegasi **cuma bisa lihat**, tidak bisa aksi apa pun (termasuk nanti kalau ada pembayaran online). Kalau suatu saat dibuka jadi bisa ikut bayar SPP, ada beberapa hal yang perlu diantisipasi di desain Finance nanti (dicatat di sini supaya tidak jadi tempelan belakangan):
- Perlu kolom audit tambahan semacam `paid_by` di transaksi pembayaran (nunjuk ke `users.id`) — bukan cuma `handover_by` yang ada sekarang — supaya jelas akun mana yang submit pembayaran kalau ada sengketa.
- Potensi race condition: dua akun (ayah & ibu) coba bayar invoice yang sama di waktu nyaris bersamaan — butuh locking/idempotency di Action pembayaran.
- Perlu diputuskan: notifikasi tagihan (WA/email) dikirim ke akun utama saja, atau ke semua akun yang delegated?
- Secara struktur data ini aman dilakukan kapan saja (karena akses sudah dipisah dari kepemilikan data lewat `parent_access_grants`) — jadi bukan blocker, cuma perlu disiapkan pas modul Finance & Notification digarap.
- **Relevan juga ke "Manajemen sesi/perangkat aktif"** (lihat "Yang Sengaja Belum Dibangun") — begitu 1 keluarga punya banyak akun delegasi, kemampuan lihat/cabut sesi/device jadi makin penting buat keamanan (mis. akun delegasi yang device-nya hilang).
