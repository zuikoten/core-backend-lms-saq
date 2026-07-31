# 🧩 Modul Core

Modul ini pemegang master data statis lintas modul. Untuk sekarang isinya baru **Tahun Ajaran** (`academic_years`) — `Jenjang`, `Semester`, dan `Master Mapel` menyusul sesuai kebutuhan modul yang memakainya (belum ada modul yang butuh saat ini).

---

## Kenapa Tahun Ajaran Duluan?

Hampir semua modul lain (Finance, Academic, dst.) butuh referensi "tahun ajaran aktif" biar data yang dibuat (invoice, tarif, dst.) tidak jadi data dummy tanpa periode yang jelas. Makanya modul ini digarap paling awal, sesuai urutan di `HANDOFF.md`.

---

## Dua Sisi Akses, Satu Data

Sama seperti modul Auth, modul ini juga melayani dua jenis user dengan mekanisme berbeda:

| | Staf sekolah | Orang tua/wali |
|---|---|---|
| Guard | `web` | `sanctum` |
| Controller | `AcademicYearController` | `AcademicYearApiController` |
| Route | `web.php` (prefix `/academic-years`) | `api.php` (prefix `/api/academic-years`) |
| Bisa apa | Full CRUD + aktivasi | **Read-only** — lihat daftar & tahun ajaran aktif saja |

Orang tua tidak pernah mengelola tahun ajaran — mereka cuma perlu tahu tahun ajaran mana yang sedang berjalan (mis. buat konteks tagihan SPP di aplikasi React nanti). Makanya `AcademicYearApiController` sengaja tidak punya `store`/`update`/`destroy`.

---

## Aturan Bisnis: Hanya Satu yang Aktif

- **`ActivateAcademicYearAction`** menjamin cuma ada 1 baris `is_active = true` di satu waktu — begitu satu tahun ajaran diaktifkan, semua yang lain otomatis dinonaktifkan dalam 1 transaction.
- Tahun ajaran baru (`CreateAcademicYearAction`) selalu dibuat **tidak aktif** dulu — mengaktifkan adalah keputusan terpisah & sadar, bukan efek samping dari "tambah data".
- **`DeleteAcademicYearAction`** menolak menghapus tahun ajaran yang sedang aktif (harus aktifkan yang lain dulu). Guard terhadap data yang mereferensikan `academic_year_id` (tarif, invoice) **belum ditambahkan** — menyusul begitu modul Finance/Academic mulai memakainya.

---

## Permission

Route staf dilindungi permission **`academic-years.manage`** (belum termasuk `panel.access` dari modul Auth — keduanya jalan bareng: `panel.access` syarat masuk panel, `academic-years.manage` syarat mengelola modul ini secara spesifik).

⚠️ **Permission ini belum di-seed otomatis.** Sebelum dipakai, daftarkan manual lewat Tinker:

```php
$permission = \Spatie\Permission\Models\Permission::create([
    'name' => 'academic-years.manage',
    'guard_name' => 'web',
]);

// assign ke role yang relevan, mis:
\Spatie\Permission\Models\Role::findByName('superadmin')->givePermissionTo($permission);
```

(superadmin sebenarnya bypass total lewat `Gate::before` di modul Auth, jadi baris assign di atas opsional untuk superadmin — tapi wajib untuk role staf lain yang nanti dibuat, mis. bendahara.)

---

## Views (Blade, khusus staf)

`resources/views/modules/core/academic-years/` — `index`, `create`, `edit`. Dibuat pakai `@extends('layouts.app')` sebagai **asumsi nama layout** — sesuaikan kalau nama layout project berbeda.

---

## Yang Perlu Dilakukan Manual (bukan dikerjakan Claude, sesuai preferensi project)

1. **Registrasi provider** — tambahkan `Modules\Core\Providers\CoreModuleServiceProvider::class` ke `bootstrap/providers.php` (saya tidak punya akses ke file itu).
2. **Seed permission** `academic-years.manage` (lihat bagian Permission di atas).
3. Migration `academic_years` **tidak dibuat ulang** — sudah ada dari awal project sesuai `HANDOFF.md`.

---

## Struktur Folder

```text
app/Modules/Core/
├── Controllers/
│   ├── AcademicYearController.php      # Staf, Blade, guard web
│   └── AcademicYearApiController.php   # Orang tua, JSON, guard sanctum (read-only)
├── Requests/
│   ├── StoreAcademicYearRequest.php
│   └── UpdateAcademicYearRequest.php
├── Resources/
│   └── AcademicYearResource.php
├── Models/
│   └── AcademicYear.php
├── Actions/
│   ├── CreateAcademicYearAction.php
│   ├── UpdateAcademicYearAction.php
│   ├── ActivateAcademicYearAction.php
│   └── DeleteAcademicYearAction.php
├── Providers/
│   └── CoreModuleServiceProvider.php
├── web.php
└── api.php
```

## Yang Sengaja Belum Dibangun

- `Jenjang`, `Semester`, `Master Mapel` — menyusul begitu ada modul yang benar-benar butuh.
- Guard hapus terhadap data yang mereferensikan `academic_year_id` dari modul lain.
- Test otomatis (Pest) — menyusul, ikut standar modul Auth.
