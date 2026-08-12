# Panduan Pasang Fitur User & Role Management

File di ZIP ini semuanya **file baru berdiri sendiri**, tinggal salin ke
lokasi yang sama persis di project (struktur foldernya sudah mengikuti
`app/Modules/Auth/...` dan `resources/views/modules/auth/...`).

3 hal di bawah ini **TIDAK saya buat sebagai file utuh** karena filenya
sudah ada di project dan saya tidak tahu isi lengkapnya saat ini — supaya
tidak menimpa yang sudah ada, tempel manual potongan berikut:

---

## 1. Route — tambahkan ke `app/Modules/Auth/web.php`

Taruh di dalam grup yang sudah pakai middleware `auth:web` + `EnsureUserIsActive`
yang sama seperti route staf lain di modul ini.

```php
use Modules\Auth\Controllers\UserController;
use Modules\Auth\Controllers\RoleController;

Route::middleware(['auth:web', 'permission:user.manage', EnsureUserIsActive::class])
    ->prefix('users')
    ->name('users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
    });

Route::middleware(['auth:web', 'permission:role.manage', EnsureUserIsActive::class])
    ->prefix('roles')
    ->name('roles.')
    ->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
    });
```

---

## 2. Permission baru — tambahkan ke `database/seeders/PermissionSeeder.php`

Pakai `firstOrCreate()` seperti pola seeder lain di project ini:

```php
Permission::firstOrCreate(['name' => 'user.manage', 'guard_name' => 'web']);
Permission::firstOrCreate(['name' => 'role.manage', 'guard_name' => 'web']);
```

Setelah ditambahkan, jalankan ulang seeder-nya (atau `php artisan db:seed
--class=PermissionSeeder`) supaya kedua permission ini benar-benar ada di
tabel `permissions` — kalau belum ada baris-nya, `Rule::exists('permissions',
'name')` di Request akan selalu gagal validasi.

**Role Superadmin tidak perlu di-assign 2 permission ini secara manual** —
sesuai `README.md` modul Auth, Superadmin sudah bypass semua permission
lewat `Gate::before` di `AuthModuleServiceProvider`.

---

## 3. Sidebar — tempel ke `resources/views/components/staff-sidebar.blade.php`

Ubah link mati "Pengaturan" yang sekarang jadi accordion group baru
(pola sama persis seperti grup "Data Master" yang sudah ada). Ganti bagian
ini:

```blade
<a href="#"
    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
    <i class="ti ti-settings text-[18px]"></i>
    Pengaturan
</a>
```

Jadi:

```blade
<div>
    <button @click="openGroup = openGroup === 'pengaturan' ? null : 'pengaturan'"
        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
        <span class="flex items-center gap-3">
            <i class="ti ti-settings text-[18px]"></i>
            Pengaturan
        </span>
        <i class="ti ti-chevron-down text-[16px] transition-transform"
            :class="openGroup === 'pengaturan' && 'rotate-180'"></i>
    </button>
    <div x-show="openGroup === 'pengaturan'" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
        <a href="{{ route('users.index') }}"
            class="block px-3 py-2 rounded-lg text-sm transition
          {{ request()->routeIs('users.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
            Pengguna
        </a>
        <a href="{{ route('roles.index') }}"
            class="block px-3 py-2 rounded-lg text-sm transition
          {{ request()->routeIs('roles.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
            Role & Hak Akses
        </a>
    </div>
</div>
```

Jangan lupa tambahkan `'users.*', 'roles.*'` ke daftar `routeIs([...])` di
`x-data` paling atas file (bagian yang menentukan `openGroup` mana yang
otomatis terbuka saat halaman di-refresh), supaya konsisten dengan pola
"Akademik"/"Keuangan"/"Data Master" yang sudah ada:

```php
openGroup: '{{ request()->routeIs(['class-groups.*', 'class-group-students.*', 'report-cards.*'])
    ? 'akademik'
    : (request()->routeIs(['finance.*'])
        ? 'keuangan'
        : (request()->routeIs(['academic-years.*', 'jenjang.*', 'grade-levels.*', 'semesters.*', 'classrooms.*'])
            ? 'data-master'
            : (request()->routeIs(['users.*', 'roles.*'])
                ? 'pengaturan'
                : null))) }}'
```

---

## Yang perlu dicek/diputuskan sebelum pakai

- **Normalisasi nomor HP** di `StoreUserRequest`/`UpdateUserRequest` saya
  tulis dengan pola generik (buang non-digit, ganti awalan `0` jadi `62`)
  berdasar deskripsi konvensi di README — belum saya cocokkan ke
  implementasi asli mutator `phoneNumber()` di `App\Models\User` (saya
  tidak punya akses ke file Model). Cek dulu, kalau mutator itu sudah
  cukup menormalisasi otomatis, blok `prepareForValidation()` di kedua
  Request boleh disederhanakan biar tidak dobel logic.
- **Nama role "Superadmin"** di semua Action & view saya asumsikan persis
  seperti itu (huruf besar di awal) sesuai contoh di README & screenshot
  sidebar (`Superadmin`). Kalau ternyata beda persis di database,
  sesuaikan string-nya di `UpdateUserAction`, `DeleteUserAction`,
  `UpdateRoleAction`, `DeleteRoleAction`, dan view `roles/index.blade.php`.
