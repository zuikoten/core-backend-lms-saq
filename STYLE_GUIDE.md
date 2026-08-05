# 🎨 STYLE_GUIDE.md — Konvensi Kode Project Ini

> Dokumen ini dibuat supaya sesi Claude yang baru bisa langsung meniru gaya
> kode yang sudah ada, tanpa perlu minta contoh file dulu di awal setiap kali
> mulai modul baru. Kalau ada pola yang belum tercakup di sini, cek modul
> yang paling mirip (Auth = paling matang, Core/Student/Academic = paling
> baru) sebagai referensi, atau tanyakan ke pemilik project.

---

## 1. Filosofi Umum

- **Controller tipis, logika bisnis di Action.** Controller cuma: terima
  Request yang sudah tervalidasi → panggil 1 Action → kembalikan
  Resource/View/redirect. Tidak ada `if`/`foreach`/query kompleks di
  Controller.
- **1 Action = 1 fitur**, method utamanya selalu bernama `execute()`.
- **Komentar menjelaskan KENAPA, bukan APA.** Kode sudah menjelaskan apa
  yang terjadi; komentar isinya alasan bisnis/keputusan di baliknya, ditulis
  dalam Bahasa Indonesia, biasanya sebagai PHPDoc block di atas method.
- **Efek samping tidak diam-diam.** Kalau sebuah Action bisa mengubah data
  di luar dugaan (mis. reuse data lama, gabung ke entitas lain), itu selalu
  dikembalikan/di-flash sebagai info ke user, bukan dibiarkan senyap.

---

## 2. Struktur Folder & Namespace

Tiap modul di `app/Modules/{NamaModul}/` (PascalCase, singular untuk domain:
`Core`, `Student`, `Academic`, `Auth`, `Finance`) wajib punya minimum:

```
app/Modules/{NamaModul}/
├── Controllers/
├── Requests/
├── Resources/     (cuma kalau modul itu punya endpoint API)
├── Models/        (kalau Model-nya milik modul ini, bukan sekadar dipakai)
├── Actions/
├── Providers/
├── web.php         (kalau ada halaman Blade staf)
├── api.php          (kalau ada endpoint API)
└── README.md
```

Folder tambahan boleh (`Middleware/`, `Notifications/`, `Jobs/`, dll.)
selama business logic tetap di `Actions/`.

**Namespace ikut path**: `Modules\{NamaModul}\{Folder}\{ClassName}`.

**Arah dependency SATU ARAH** — ini aturan paling penting: modul "fondasi"
(Core) **tidak pernah** import Model dari modul yang mengonsumsinya
(Student, Academic, Finance). Modul turunan boleh import dari fondasi.
Kalau modul fondasi perlu tahu "apakah data ini masih dipakai modul lain"
(mis. guard hapus), query lewat `DB::table('nama_tabel')` by nama tabel,
BUKAN import Model cross-module. Lihat `DeleteGradeLevelAction` di modul
Core sebagai contoh konkret.

---

## 3. Model

```php
<?php

namespace Modules\{Modul}\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContohModel extends Model
{
    protected $fillable = [
        'kolom_satu',
        'kolom_dua',
    ];

    // Cast sebagai METHOD (bukan property $casts) — pola yang dipakai di
    // model-model terbaru (Student, AcademicYear, dll). Ikuti ini untuk
    // model baru.
    protected function casts(): array
    {
        return [
            'kolom_boolean' => 'boolean',
        ];
    }

    public function relasiLain(): BelongsTo
    {
        return $this->belongsTo(ModelLain::class);
    }
}
```

- `protected $table` cuma di-set eksplisit kalau nama tabel tidak bisa
  ditebak dari nama class (contoh: `ParentProfile` → tabel `parents`,
  karena `Parent` reserved word di PHP).
- Relasi HasMany yang FK-nya tidak bisa ditebak dari nama class (kasus di
  atas) **wajib** sebut FK eksplisit: `hasMany(Student::class, 'parent_id')`.
- Model boleh import Model dari modul lain kalau memang butuh relasi lintas
  modul (lihat `AcademicYear` yang punya relasi ke `Modules\Finance\Models\*`
  meski Finance belum digarap — itu aman, PHP baru butuh class itu ada pas
  method-nya benar dipanggil, bukan pas file di-load).

---

## 4. Action

```php
<?php

namespace Modules\{Modul}\Actions;

use Illuminate\Validation\ValidationException;
use Modules\{Modul}\Models\ContohModel;

class LakukanSesuatuAction
{
    /**
     * Jelaskan di sini KENAPA aturan bisnis ini ada — bukan APA yang
     * dilakukan kode di bawah (itu sudah jelas dari baca kode-nya).
     */
    public function execute(ContohModel $model, array $data): ContohModel
    {
        if ($kondisiTidakBoleh) {
            throw ValidationException::withMessages([
                'field' => 'Pesan error yang jelas & actionable buat user.',
            ]);
        }

        $model->update($data);

        return $model;
    }
}
```

- Constructor injection kalau 1 Action butuh Action lain (contoh:
  `CreateStudentAction` inject `FindOrCreateParentByPhoneAction`).
- Operasi yang mengubah >1 baris/tabel sekaligus **wajib** dibungkus
  `DB::transaction()` (contoh: `ActivateSemesterAction`,
  `TransferStudentAction`).
- Validasi bisnis (bukan validasi format input — itu tugas Request) pakai
  `ValidationException::withMessages()`, supaya otomatis muncul sebagai
  `$errors` di Blade atau response 422 di API.

---

## 5. Controller

Ada 2 pola tergantung siapa konsumennya:

**Staf (Blade, guard `web`)** — nama class biasa: `ContohController`
```php
class ContohController extends Controller
{
    public function index(): View { ... }
    public function create(): View { ... }
    public function store(StoreContohRequest $request, CreateContohAction $action): RedirectResponse { ... }
    public function edit(Contoh $contoh): View { ... }
    public function update(UpdateContohRequest $request, Contoh $contoh, UpdateContohAction $action): RedirectResponse { ... }
    public function destroy(Contoh $contoh, DeleteContohAction $action): RedirectResponse { ... }
}
```

**Orang tua (JSON, guard `sanctum`, READ-ONLY)** — nama class diakhiri `ApiController`
```php
class ContohApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $parentProfile = ParentProfile::query()
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // ...query di-scope dari $parentProfile, TIDAK PERNAH query
        // langsung dari sisi anak/data tanpa scope ini.

        return ContohResource::collection($data);
    }
}
```
Controller API **tidak pernah** punya `store`/`update`/`destroy` — orang
tua murni read-only di semua modul sejauh ini.

Redirect setelah aksi selalu pakai `->with('status', 'Pesan singkat & jelas.')`.

---

## 6. Request

```php
<?php

namespace Modules\{Modul}\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContohRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // SELALU true — otorisasi dicek di level
                      // Action/permission middleware, bukan di Request.
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:100', Rule::unique('tabel', 'nama')],
        ];
    }

    public function messages(): array // opsional, cuma kalau pesan default kurang jelas
    {
        return [...];
    }
}
```

- Update Request pakai `->ignore($this->route('namaParameter'))` untuk unique
  rule yang harus abaikan baris itu sendiri.
- Normalisasi input (contoh: nomor HP) lewat `prepareForValidation()`.
- Unique scoped per parent (contoh: nama Grade Level unik per Jenjang) pakai
  `Rule::unique(...)->where(fn ($q) => $q->where('parent_id', $this->input('parent_id')))`.

---

## 7. Resource

```php
<?php

namespace Modules\{Modul}\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \Modules\{Modul}\Models\ContohModel $resource
 */
class ContohResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'nama' => $this->resource->nama,
        ];
    }
}
```
Cuma dibuat kalau modul itu punya endpoint API. Resource untuk data yang
belum tentu ke-load relasinya pakai `$this->whenLoaded('relasi')`.

---

## 8. Route (`web.php` / `api.php`)

```php
<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Middleware\EnsureUserIsActive;
use Modules\{Modul}\Controllers\ContohController;

// Permission '{domain}.manage' — daftarkan manual lewat Tinker/seeder
// sebelum modul ini dipakai (lihat README.md modul ini).
Route::middleware(['auth:web', 'permission:{domain}.manage', EnsureUserIsActive::class])
    ->prefix('contoh')
    ->name('contoh.')
    ->group(function () {
        Route::get('/', [ContohController::class, 'index'])->name('index');
        Route::get('create', [ContohController::class, 'create'])->name('create');
        Route::post('/', [ContohController::class, 'store'])->name('store');
        Route::get('{contoh}/edit', [ContohController::class, 'edit'])->name('edit');
        Route::put('{contoh}', [ContohController::class, 'update'])->name('update');
        Route::delete('{contoh}', [ContohController::class, 'destroy'])->name('destroy');
    });
```

**Penamaan permission**: `{domain}.manage`, singular, satu domain bisa
menaungi beberapa entitas (`core.manage` menaungi AcademicYear+Jenjang+
GradeLevel+Semester+Classroom). BUKAN ikut nama tabel/route
(`contohs.manage` salah, `contoh.manage` benar).

`api.php` untuk orang tua selalu:
```php
Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->group(function () {
    Route::get('contoh', [ContohApiController::class, 'index']);
});
```

---

## 9. Provider

```php
<?php

namespace Modules\{Modul}\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class {Modul}ModuleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__.'/../web.php');

        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__.'/../api.php');
    }
}
```
**Harus didaftarkan manual** ke `bootstrap/providers.php` — Claude tidak
punya akses ke file itu, selalu ingatkan pemilik project di akhir jawaban
tiap kali bikin modul baru.

---

## 10. Blade / View

- **Layout staf**: `@extends('layouts.staff')` — BUKAN `layouts.app`.
- **Path**: `resources/views/modules/{modul}/{entitas}/{index,create,edit,show}.blade.php`
- **Padding input WAJIB**: `class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"`
  (pernah lupa taruh `px-3.5 py-2.5` di awal — jangan diulang).
- **Component yang sudah ada**: `<x-status-badge status="success|warning|danger">Label</x-status-badge>`
  (TIDAK ada varian netral/abu-abu — untuk status semacam "tidak aktif",
  pakai `<span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">...</span>` manual)
  dan `<x-icon-badge icon="nama-ikon-tabler" color="blue|green|amber|red|indigo" />`.
- **Pola halaman index**: card putih `rounded-2xl shadow-sm`, tabel di
  dalamnya, flash message sukses (`session('status')`, hijau) & error
  (`$errors->any()`, merah) di atas tabel, tombol aksi per baris kanan
  (`justify-end`), delete/activate pakai `onsubmit="return confirm('...')"`.
- **Pola form create/edit**: card putih `rounded-2xl shadow-sm p-6`,
  `@error('field')` di bawah tiap input, tombol Simpan (indigo) + Batal
  (abu-abu) sejajar di bawah.
- **Icon**: Tabler Icons (`<i class="ti ti-nama-icon"></i>`), font Plus
  Jakarta Sans.

---

## 11. Seeder

Ditaruh di `database/seeders/` (bukan di dalam folder modul), pakai
`firstOrCreate()` (idempotent, aman dijalankan berkali-kali). Kalau seeder
butuh data dari seeder lain (mis. `SemesterSeeder` butuh tahun ajaran aktif
sudah ada), **cek dulu, skip dengan `$this->command?->warn(...)` kalau
belum ada** — jangan bikin data asal/dummy sebagai fallback.

---

## 12. Contoh File Acuan (Verbatim dari Modul Auth)

Ini contoh nyata yang paling representatif untuk masing-masing lapisan —
kalau ragu gimana nulis sesuatu, tiru pola dari sini:

- **Action dengan rate limiting & validasi bisnis**: `app/Modules/Auth/Actions/AuthenticateStaffAction.php`
- **Controller staf dengan banyak Action berbeda per method**: `app/Modules/Auth/Controllers/AuthController.php`
- **Request sederhana**: `app/Modules/Auth/Requests/LoginRequest.php`
- **Resource dengan data tambahan di luar kolom Model (token)**: `app/Modules/Auth/Resources/AuthenticatedUserResource.php`
- **Provider dengan binding interface + Gate**: `app/Modules/Auth/Providers/AuthModuleServiceProvider.php`
- **Route API dengan grup & middleware berbeda per endpoint**: `app/Modules/Auth/api.php`
- **Route Blade dengan banyak alur (login/reset password bercabang)**: `app/Modules/Auth/web.php`

Untuk pola modul yang lebih baru (Core/Student/Academic), baca langsung
`README.md` di masing-masing folder modul — sudah dijelaskan keputusan
desain & alasannya secara spesifik per modul.

---

## 13. Hal yang TIDAK Perlu Ditanyakan Ulang

Supaya sesi baru tidak nanya hal yang sudah pernah diputuskan:

- Layout Blade staf = `layouts.staff`
- Component status = `x-status-badge` (tanpa varian netral) & `x-icon-badge`
- Cast di Model = method `casts()`, bukan property `$casts`
- Model `ParentProfile` (bukan `Parent`, reserved word), tabel tetap `parents`
- Permission = `{domain}.manage` singular, di-seed manual, belum ada UI-nya
- Orang tua = selalu read-only, guard `sanctum`, di-scope dari `ParentProfile`
- Registrasi Provider ke `bootstrap/providers.php` = selalu manual, selalu diingatkan
- Migration baru = Claude jelaskan skema di chat, TIDAK bikin file migration sendiri
