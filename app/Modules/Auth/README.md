# 🔐 Modul Auth

Modul ini menangani seluruh urusan autentikasi & otorisasi sistem: login, aktivasi akun, reset password, dan pengaturan akses (role & permission). Dokumen ini menjelaskan **apa isinya dan kenapa dibangun begitu** — untuk detail arsitektur umum lintas modul, lihat `ARCHITECTURE.md` di root project.

---

## Dua Jenis Pengguna, Dua Mekanisme Login Berbeda

| | Staf sekolah | Orang tua/wali |
|---|---|---|
| Contoh role | superadmin (sekarang), guru/kepala sekolah/bendahara (rencana) | parent |
| Guard | `web` (sesi Blade) | `sanctum` (token, dikonsumsi React) |
| Cara masuk | Email+password **atau** OTP WhatsApp | OTP WhatsApp **atau** password |
| Dibuat lewat | Seeder/Tinker manual — **tidak ada self-register** | Aktivasi mandiri, harus cocok dengan data `parents` yang sudah diinput staf lebih dulu |

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

## Alur Orang Tua/Wali (API, guard `sanctum`)

Semua lewat `ParentAuthController`, prefix `/api/auth`:

- `POST /api/auth/otp/request` — minta OTP (activation/login/reset_password, dibedakan lewat `action_type`)
- `POST /api/auth/otp/verify` — verifikasi OTP, hasilnya beda tergantung `action_type`:
  - `activation` → `ActivateParentAccountAction` (cocokkan nomor HP ke `parents.phone_number` yang sudah diinput staf, buat akun baru, assign role `parent`)
  - `login` → `AuthenticateParentWithOtpAction` (buat token Sanctum)
  - `reset_password` → `ResetPasswordWithOtpAction` (verifikasi + ganti password **1 request**, beda dari staf yang dipecah 2 step — di sini boleh digabung karena React yang atur UX-nya sendiri, tidak perlu reload halaman)
- `POST /api/auth/login` — login pakai nomor HP + password (opsional, buat kondisi tanpa sinyal/pulsa)
- `POST /api/auth/logout` — revoke token aktif

**Kenapa parent tidak bisa self-register bebas?** Karena data siswa & orang tua sudah diinput staf sekolah lebih dulu (baris `parents` dengan `user_id = NULL`). Aktivasi cuma mencocokkan nomor HP ke baris yang sudah ada — mencegah orang asing bikin akun mengatasnamakan siswa yang tidak terdaftar.

---

## OTP: Satu Mekanisme, Dipakai di Banyak Tempat

Semua OTP (aktivasi parent, login staf, login parent, reset password staf, reset password parent) lewat 2 Action yang sama:

- **`GenerateOtpAction`** — generate 6 digit, simpan ke `otp_codes`, rate limit 60 detik antar-request per nomor/user, expire 5 menit.
- **`VerifyOtpAction`** — cek `is_used`, `expires_at`, cocokkan kode. **Membatasi percobaan salah** (`attempts`, default maksimal 3x per baris OTP) — begitu kelewat batas, baris itu langsung dipaksa `is_used = true` walau belum expired, supaya tidak bisa terus ditebak.

`action_type` (`activation` / `login` / `reset_password`) menentukan query-nya: `activation` dicari lewat `phone_number` (belum tentu ada user), sisanya lewat `user_id`.

**Gateway WhatsApp masih stub** (`LogWhatsappGateway`) — kode OTP ditulis ke `storage/logs/laravel.log`, bukan benar-benar terkirim. Dibungkus lewat `WhatsappGatewayInterface` supaya provider final (Fonnte/Watzhap/dll.) tinggal ganti 1 baris binding di `AuthModuleServiceProvider`, tanpa ubah kode manapun yang memanggilnya.

---

## Akses & Permission

Sistem akses berbasis **permission**, bukan nama role yang di-hardcode — supaya nambah role baru (guru, kepala sekolah, bendahara) tidak perlu ubah kode:

- `superadmin` **bypass total** semua `can()`/`@can`, lewat `Gate::before` di `AuthModuleServiceProvider::registerGates()`. Tidak perlu di-assign permission apa pun, termasuk yang belum dibuat sekalipun.
- Role lain butuh permission eksplisit. Permission minimum yang dipakai modul ini: **`panel.access`** — syarat untuk bisa login & masuk panel staf sama sekali (dicek di middleware route `staff.*` dan di semua Action terkait staf: `AuthenticateStaffAction`, `RequestStaffLoginOtpAction`, `AuthenticateStaffWithOtpAction`, `RequestStaffPasswordResetOtpAction`, `ResetPasswordWithOtpAction`).
- Permission granular per fitur (`finance.manage`, `academic.view`, dst.) **belum dibuat** — menyusul begitu modul terkait (Finance, Academic, dll.) mulai digarap, di-seed oleh modul masing-masing.

---

## Middleware Modul Ini

- **`EnsureUserIsActive`** — cek `users.is_active` di setiap request terautentikasi (web maupun sanctum), auto-logout/reject kalau `false` (mis. staf dinonaktifkan di tengah sesi aktif).

## Rate Limiting

Didaftarkan di `AuthRateLimiterServiceProvider` (terpisah dari `AuthModuleServiceProvider`, biar tidak numpuk):
- `otp-request` — maks 3x/menit per nomor HP, cegah spam yang membebani biaya WA Gateway
- `login` — maks 10x/menit per email/nomor HP, cegah brute force

## Standar Nomor HP

Semua nomor HP disimpan dalam format **`62xxxxxxxxxx`** (tanpa `0`/`+` di depan) — dinormalisasi otomatis lewat mutator `phoneNumber()` di `App\Models\User` (jalan di titik manapun nilai itu di-assign: form, seeder, Tinker) **dan** di `prepareForValidation()` tiap Form Request yang menerima nomor HP dari input.

---

## Struktur Folder (khusus tambahan di luar standar `ARCHITECTURE.md`)

```text
app/Modules/Auth/
├── Middleware/       EnsureUserIsActive
├── Notifications/    SendOtpWhatsappNotification, Channels/ (WhatsappChannel, LogWhatsappGateway), Contracts/ (WhatsappGatewayInterface)
├── Providers/        AuthModuleServiceProvider (routes, Gate::before, WA binding), AuthRateLimiterServiceProvider
└── web.php           Route Blade staf (selain api.php standar untuk parent)
```

## Yang Sengaja Belum Dibangun

- CRUD Role & Permission lewat UI (rencananya di menu sidebar "Pengaturan") — untuk sekarang masih manual lewat seeder/Tinker.
- Notifikasi ke user asli kalau password mereka direset (mis. email "password Anda baru saja diubah").
- Test otomatis untuk alur-alur kritis modul ini.