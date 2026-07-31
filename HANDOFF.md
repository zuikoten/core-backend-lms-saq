# 📋 HANDOFF — Status Proyek & Agenda Selanjutnya

> Dokumen ini ditulis untuk diberikan ke sesi Claude (atau developer) yang baru,
> supaya langsung paham konteks tanpa perlu baca ulang seluruh riwayat chat
> sebelumnya. Untuk detail arsitektur teknis lengkap, baca `ARCHITECTURE.md`
> dan `app/Modules/Auth/README.md`.

---

## 🏫 Tentang Proyek

Sistem LMS + Tata Kelola Sekolah untuk **TK** (bagian dari ekosistem "SAQ"),
fokus utama akhirnya ke **Finance/SPP**, tapi dibangun bertahap dari fondasi
data dulu — Finance sengaja **paling akhir**, karena butuh data dari modul
lain (siswa, kelas, tahun ajaran) supaya tagihan bisa dipetakan ke entitas
yang nyata, bukan data dummy.

- **Instalasi tunggal per sekolah** (bukan SaaS/multi-tenant)
- **Laravel 13**, PHP 8.3, MySQL, testing pakai **Pest**
- Arsitektur **Modular DDD ringan** — 1 modul = 1 folder mandiri di
  `app/Modules/`, logika bisnis di `Actions/` (1 class = 1 fitur), Controller
  cuma nerima input & kasih respons
- Frontend admin: **Blade + Tailwind + Alpine.js** (Vite)
- Frontend orang tua: **React terpisah**, konsumsi lewat API (Sanctum)

---

## ✅ Yang Sudah Selesai: Modul Auth

**Status: stabil, 7 test otomatis (Pest) semua lolos.** Detail lengkap di
`app/Modules/Auth/README.md` — ringkasannya:

- **Dua jenis user, dua mekanisme berbeda:**
  - Staf sekolah (superadmin, nanti guru/kepala sekolah/bendahara) — guard
    `web`, login email+password **atau** OTP WhatsApp
  - Orang tua/wali — guard `sanctum` (API/React), login OTP WhatsApp atau
    password, aktivasi akun dengan mencocokkan nomor HP ke data yang sudah
    diinput staf (bukan self-register bebas)
- **Reset password** 2 jalur: broker email bawaan Laravel, atau OTP WhatsApp
  (dipecah 3 halaman terpisah: minta OTP → verifikasi → set password baru)
- **Akses berbasis permission** (Spatie), bukan hardcode nama role — superadmin
  bypass total lewat `Gate::before`, role lain butuh permission eksplisit
  (`panel.access` = syarat minimum masuk panel)
- **OTP hardening**: rate limit generate (60 detik/nomor), expire 5 menit,
  maksimal 3x percobaan salah per kode sebelum dipaksa hangus
- **WhatsApp Gateway masih stub** (`LogWhatsappGateway`) — kode OTP ditulis ke
  `storage/logs/laravel.log`, belum benar-benar terkirim. Provider final
  (Fonnte/Watzhap/dll.) belum dipilih, tapi sudah dibungkus interface
  (`WhatsappGatewayInterface`) supaya tinggal ganti 1 binding nanti.
- **Nomor HP** disimpan format `62xxxxxxxxxx` (tanpa `0`/`+`), dinormalisasi
  otomatis lewat mutator di `App\Models\User` + tiap Form Request terkait.

---

## 🗺️ Urutan Modul Selanjutnya (disepakati)

```
1. Core            — Jenjang, Tahun Ajaran, Semester, Master Mapel
2. Student & Teacher — Profil siswa, orang tua/wali, guru
3. Academic         — Kelas, Rombel, plotting siswa
4. Finance          — Pembayaran SPP (PALING AKHIR, sengaja)
```

Alasan urutan ini: Finance butuh siswa+kelas+tahun ajaran yang nyata dulu
supaya tarif bisa dipetakan dengan benar, bukan dibangun di atas data dummy
yang nanti perlu di-refactor.

Modul lain di luar 4 ini (Admission/PPDB, Learning, Attendance, Exam,
Notification, ELibrary, Canteen) belum digarap sama sekali — masih peta
jangka panjang di `ARCHITECTURE.md`, jangan buat menu/link/kode untuk modul
ini dulu sampai gilirannya tiba.

### ⚡ Keputusan: Jalur Minimal supaya Finance Cepat Jalan

Karena kejar waktu, tidak semua isi Core/Student/Academic perlu dibangun
**penuh** dulu sebelum Finance boleh disentuh — cukup irisan tipis yang
benar-benar dipakai Finance sebagai referensi FK.

> 📐 **Update penting:** skema tabel Core/Student/Finance (`academic_years`,
> `parents`, `students`, `billing_types`, `billing_tariffs`,
> `student_tariff_mappings`, `invoices`, `invoice_items`, `payment_channels`,
> `invoice_payments`, `payment_gateway_transactions`, `webhook_logs`)
> **sudah ada sebagai migration** dari awal project — bukan perlu dirancang
> dari nol. Yang perlu digarap tinggal Action/Controller/Resource-nya
> (mengikuti pola modul Auth: Controller tipis, logika bisnis di Actions/).
> Skema ERD lengkapnya ada di file terpisah `skema-relasional-mermaid.md`.

**Wajib ada dulu:**
- `academic_years` (Core) — tahun ajaran aktif, invoice/tarif harus tertaut
  ke periode tertentu
- `students` (Student) — versi dasar saja. **Field data orang tua TIDAK
  perlu diduplikasi ke sini** — itu sudah ada di tabel `parents` (dibuat
  modul Auth, dipakai untuk aktivasi akun).

**Boleh dilewati dulu (tidak wajib untuk Finance jalan):**
- `semesters` — hanya perlu kalau billing-nya per-semester, bukan per tahun
  ajaran penuh
- **Seluruh modul Academic** (Kelas/Rombel/plotting) — tidak relevan buat
  Finance. Keputusan bisnis: variasi tarif SPP levelnya **per-siswa**
  (diskon kurang mampu, beasiswa penuh, tarif lebih tinggi untuk kebutuhan
  khusus), BUKAN per-kelas/rombel (kasus "kelas 10A beda tarif dari 10B"
  dianggap sangat jarang/tidak perlu didukung). `student_tariff_mappings`
  yang nunjuk langsung ke `student_id` sudah cukup dan memang levelnya
  tepat untuk kasus ini.
- **Seluruh modul Teacher** — Finance tidak ada urusan sama guru sama
  sekali.
- Field profil siswa yang lebih lengkap (NISN, tanggal lahir, alamat, foto)
  — bisa nyusul kapan saja lewat `ALTER TABLE ADD COLUMN` (aman, additive)

**Keputusan tambahan kolom di `student_tariff_mappings`** (belum
diterapkan ke migration, masih perlu ditambahkan manual): `note` (text,
nullable — alasan pemetaan tarif ini, mis. "beasiswa prestasi") dan
`approved_by` (FK nullable ke `users`, `nullOnDelete` — siapa yang
menyetujui). Alasannya: diskon/beasiswa per siswa butuh audit trail yang
eksplisit, bukan cuma bisa ditebak dari angka tarifnya.

**`invoices.due_date` sengaja nullable** — bukan semua billing (tabungan
sukarela, iuran kelas, denda perpustakaan, dll.) punya tenggat waktu jelas
seperti SPP bulanan. Ini keputusan final, tidak perlu diubah.

**Risiko jalur ini (dan kenapa risikonya kecil):**
- Field profil siswa yang belum ada sekarang bisa nyusul lewat
  `ALTER TABLE ADD COLUMN` — aman, tidak mengubah data yang sudah ada.
- **Prinsip desain untuk fitur siswa yang sifatnya "banyak per siswa"
  (bukan atribut tunggal):** Catatan BK, Rekam Medis UKS, dan rencana masa
  depan (foto profil, galeri, portofolio, kalau nanti siswa punya dashboard
  sendiri) — semua ini JANGAN jadi kolom baru di `students`. Ini kejadian/
  koleksi berulang per siswa, jadi harus jadi **tabel terpisah** dengan
  `student_id` sebagai FK, one-to-many (mis. `student_disciplinary_records`,
  `student_health_records`, `student_profiles`, `student_portfolios`).
  Nambah tabel begini **tidak menyentuh** skema `students` yang sudah ada
  sama sekali — jadi risiko break jauh lebih kecil dari yang awalnya
  dikira.
- Kalau ternyata nanti Academic/Teacher *memang* dibutuhkan Finance di
  luar dugaan (mis. laporan keuangan per rombel), itu nambah, bukan
  bongkar ulang skema yang sudah dibuat di jalur minimal ini.

---

## 📝 Yang Sengaja Belum Dibangun (bukan bug, cuma dicatat)

- CRUD Role & Permission lewat UI (rencana di sidebar menu "Pengaturan") —
  untuk sekarang role/permission masih manual lewat seeder/Tinker
- Permission granular per-modul (`finance.manage`, `academic.view`, dst.) —
  menyusul begitu modul terkait mulai digarap, di-seed oleh modul
  masing-masing, BUKAN didaftarkan lebih dulu oleh modul Auth
- Provider WhatsApp Gateway final belum dipilih
- Notifikasi ke user asli kalau password mereka direset

---

## 🎨 Keputusan Desain yang Perlu Diikuti Konsisten

- **Views**: `resources/views/modules/{modul}/...` (dot notation standar,
  TIDAK pakai custom view namespace), layout & component shared di LUAR
  folder `modules/` (`layouts/`, `components/`)
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
  perlu ("Claude ingin bikin patch migration"), CUKUP JELASKAN skemanya di
  chat, JANGAN buat file migration baru — pemilik project akan edit
  langsung migration utamanya sendiri lalu `migrate:fresh`.
- **Jangan kirim bulk zip project.** Kirim SATU per SATU file yang relevan
  dengan permintaan saat itu saja, dalam bentuk code block di chat (bukan
  file terpisah untuk didownload), lengkap dengan path filenya dan status
  (baru/edit). Pemilik project sering melakukan modifikasi manual sendiri
  di file-file itu, jadi bundling ulang semuanya bikin kerjaan dobel.
- Pemilik project cukup teknis, suka didiskusikan dulu keputusan desain yang
  besar (pakai pertanyaan singkat) sebelum dieksekusi — tapi begitu setuju,
  langsung eksekusi penuh tanpa ditunda-tunda.
