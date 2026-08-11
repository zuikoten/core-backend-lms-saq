# Modul Finance

Pembayaran SPP & tagihan lain. **Alur manual sudah lengkap end-to-end** —
dari master data sampai laporan. Yang tersisa cuma integrasi Finpay
(payment gateway), sengaja ditunda karena providernya belum final.

Permission: `finance.manage` — satu permission menaungi seluruh entitas
modul ini (pola sama dengan `core.manage`), belum ada pemisahan permission
per entitas.

## Status Entitas

### ✅ BillingType — CRUD selesai
Master data jenis tagihan (mis. "SPP Bulanan", "Uang Pangkal").
`is_recurring` membedakan tagihan berulang (bulanan, generate otomatis)
vs sekali bayar (invoice manual). Guard hapus: dicek lewat
`billing_tariffs` & `invoice_items` — tidak bisa dihapus kalau sudah
dipakai.

### ✅ PaymentChannel — CRUD selesai
Kanal pembayaran (transfer bank, VA, e-wallet, tunai). `provider`
membedakan manual (dicatat kasir) vs `finpay` (belum diintegrasikan).
Guard hapus: dicek lewat `invoice_payments` & `payment_gateway_transactions`.
Kanal yang sudah tidak dipakai cukup dinonaktifkan lewat `is_active`,
tidak perlu dihapus.

### ✅ BillingTariff — CRUD selesai
Tarif per kombinasi jenis tagihan + tahun ajaran. `tariff_name` jadi
pembeda kalau 1 jenis tagihan punya nominal beda per kelompok siswa
(mis. "SPP TK-A" vs "SPP TK-B"). Validasi unique per
`(billing_type_id, academic_year_id, tariff_name)` di level Request
(bukan DB constraint).

### ✅ StudentTariffMapping — CRUD + Bulk Mapping selesai
Pemetaan tarif ke siswa, unik per `(student_id, academic_year_id,
billing_type_id)`. Kolom `note` & `approved_by` (nullable, FK ke
`users`) untuk audit trail diskon/tarif khusus — `approved_by` diisi
manual pilih user ber-role **"Kepala Sekolah"** (bukan auto dari user
login), `note` wajib diisi kalau `approved_by` terisi
(`required_with:approved_by`).

**Mekanisme approval saat ini sengaja simpel** (isi manual, tanpa alur
pengajuan/verifikasi berjenjang) — birokrasi lengkap ala Komite Tarif &
Beasiswa (KTB) sempat didiskusikan tapi diputuskan **belum** dibangun
sebagai modul terpisah, karena `approved_by`+`note` saat ini cuma 2
kolom nempel di tabel yang sudah ada, bukan entitas baru. Kalau nanti
beneran dibutuhkan (skala yayasan besar, perlu alur pengajuan dokumen,
sidang pleno, SK, dll.), baru layak dipecah jadi modul sendiri (mis.
`FeeException`).

**Bulk Mapping**: 1 halaman (`bulk-create`), bukan alur
form→submit→preview terpisah. Filter (Semua siswa aktif / Rombel
tertentu) & checklist siswa tampil bersamaan, live-update lewat AJAX
`fetch()` ke `GET .../eligible-students` tiap filter berubah — lihat
`resources/js/modules/finance/student-tariff-mapping-bulk-create.js`.
Siswa yang match = aktif di rombel pada tahun ajaran tarif ini
(`class_group_students.moved_out_at IS NULL`) DAN belum punya pemetaan
untuk jenis tagihan + tahun ajaran yang sama.

### ✅ Invoice & InvoiceItem — Generate Massal + Manual selesai
**1 siswa = 1 invoice per bulan** (`unique` per `student_id` +
`academic_year_id` + `period_month` + `period_year`, dicek di level
Action, bukan DB constraint). 1 invoice bisa punya banyak item.

- **Generate Massal** (`invoices/bulk-create`): sama polanya kayak Bulk
  Mapping Tarif — filter (tahun ajaran, rombel opsional, bulan/tahun) +
  checklist siswa, live-update AJAX. Cuma proses siswa yang punya tarif
  **recurring** (SPP) & belum kena invoice periode itu.
- **Manual** (`invoices/manual-create`): buat tagihan sekali bayar
  (Uang Pangkal, dll.), item bisa ditambah/dikurangi dinamis (Alpine).
  Kalau siswa sudah punya invoice periode yang sama, ditolak — diarahkan
  nambah item lewat halaman invoice yang sudah ada, bukan bikin invoice
  baru (`AddInvoiceItemAction`/`storeItem` di `InvoiceController`).
- **Item cuma bisa ditambah/dihapus selama invoice masih `status =
  'unpaid'`** — begitu ada pembayaran, item dikunci (`AddInvoiceItemAction`
  & `DeleteInvoiceItemAction` menolak).
- **Format `invoice_number`**: `INV/{tahun}/{bulan}/{NISN}` (fallback
  `S{student_id}` kalau NISN kosong) — lihat `GenerateInvoiceNumberAction`.

### ✅ InvoicePayment — pencatatan manual selesai
Nempel di halaman *Detail Invoice* (`InvoiceController::storePayment`/
`destroyPayment`), bukan controller terpisah. Status invoice
(`unpaid`→`partial`→`paid`) dihitung ulang otomatis tiap ada
perubahan pembayaran lewat `RecalculateInvoiceStatusAction` — **bukan**
disimpan manual, supaya tidak ada celah status tidak sinkron dengan
nominal yang benar-benar masuk.

**Kelebihan bayar (amount_paid > sisa tagihan) DITOLAK** (validasi
error) — keputusan eksplisit, bukan diizinkan lalu dianggap kredit.

Pembayaran yang `payment_gateway_transaction_id`-nya terisi (dari
Finpay, belum aktif) tidak bisa dihapus manual — koreksinya harus lewat
alur gateway.

### ✅ Laporan Keuangan — 4 laporan selesai
Di `finance/reports/`, hub page + 4 halaman:
1. **Rekap SPP per Bulan** — total tagihan vs terbayar vs tunggakan per
   periode, per tahun ajaran.
2. **Daftar Tunggakan** — invoice `unpaid`/`partial`, filter opsional
   per rombel.
3. **Rekap per Kanal Pembayaran** — breakdown uang masuk per kanal,
   filter rentang tanggal (`paid_at`).
4. **Breakdown Komponen Biaya** — uang masuk dipisah per jenis tagihan
   (SPP/Kegiatan/Buku/dll). **Alokasi pakai aturan FIFO**: item
   `is_recurring` (SPP) diprioritaskan lunas duluan, sisa pembayaran baru
   mengalir ke item lain (urut `id`). **Keputusan sengaja** — proporsional/
   pro-rata dipertimbangkan tapi ditolak karena dianggap ambigu (lihat
   `CalculateComponentBreakdownAction`). Kalau nanti kebutuhan makin
   kompleks (banyak tagihan besar sekaligus dalam 1 invoice), rencana
   jangka panjangnya memisah jadi invoice-invoice tersendiri per jenis
   tagihan, bukan mengubah aturan alokasi ini.

### ✅ API Orang Tua — read-only selesai
`app/Modules/Finance/api.php`, guard `sanctum`, prefix
`finance/invoices`. **Pola ownership-check meniru persis
`StudentApiController`** (bukan `DB::table()` manual): query
`ParentProfile::where('user_id', ...)->with('students')`, lalu cek
`$parentProfile->students->contains('id', $studentId)`. Logic query
dipecah ke Action (`FindInvoicesForStudentAction`,
`GetInvoiceDetailAction`, `GetInvoiceSummaryForStudentAction`), Controller
cuma manggil — pola ini diambil dari `ParentAuthController` (modul
Auth), bukan `StudentApiController` yang masih sangat minimal.

Endpoint:
- `GET /api/finance/invoices?student_id=` — list, dipaginasi
  (`paginate(15)`, response ada `links`+`meta` — ini bawaan Laravel,
  bukan ditulis manual, dan **sengaja dipertahankan** apa adanya).
- `GET /api/finance/invoices/{invoice}` — detail lengkap (item, sisa
  tagihan, info kelas lewat `DB::table()` ke `class_group_students`,
  bukan import Model Academic).
- `GET /api/finance/invoices/summary?student_id=` — ringkasan tunggakan
  1 anak (total outstanding, jumlah invoice unpaid, next due date).
  **Belum ada versi bulk** (ringkasan semua anak sekaligus) — dipakai
  kalau nanti mau nampilin badge di halaman pemilih-anak, belum
  dibutuhkan sekarang karena alurnya masih 2 langkah (pilih anak → baru
  lihat tagihan anak itu).
- **Belum ada**: download PDF invoice & endpoint mulai pembayaran
  ("Bayar Sekarang") — ditunda sampai fitur cetak PDF & Finpay siap.
  React frontend-nya sendiri **juga belum dibangun** — sesi ini cuma
  bikin endpoint-nya.

### ⏳ Belum digarap — PaymentGatewayTransaction & WebhookLogs
Integrasi Finpay. Skema tabelnya sudah ada dari awal project, providernya
belum final (lihat catatan di HANDOFF).

## Pola Teknis yang Perlu Diikuti Konsisten (spesifik modul ini)

- **Bulk action (mapping tarif & generate invoice) selalu pola sama**:
  filter + checklist di 1 halaman, live AJAX, JS terpisah di
  `resources/js/modules/finance/`, pakai `Alpine.data()` di dalam
  `document.addEventListener('alpine:init', ...)`.
- **`@vite(...)` file JS modul HARUS didaftarkan SEBELUM `app.js`** di
  layout — `layouts.staff` pakai `@stack('scripts')` yang taruhnya di
  **atas** `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
  Kalau kebalik, `Alpine.start()` di `app.js` jalan duluan sebelum
  listener `alpine:init` modul sempat kedaftar → `x-data="xxx()"` gagal
  diam-diam tanpa error jelas. Ini pernah kejadian & makan waktu debug
  cukup lama — jangan diulang.
- **Expose route Blade ke JS terpisah lewat `window.financeRoutes.xxx`**
  (di-set inline `<script>` kecil sebelum `@vite(...)` di `@push('scripts')`),
  karena file JS modul murni tidak diproses Blade lagi.
- **Guard hapus lintas entitas pakai `DB::table()`**, bukan import Model
  (prinsip modul fondasi vs turunan, sama kayak Core/Student/Academic).
- **Delete Action tidak selalu ada guard** — kalau memang tidak ada
  tabel lain yang FK ke entitas itu (contoh: `DeleteStudentTariffMappingAction`),
  jangan dipaksa nambah guard kosongan, cukup dikomentari kenapa aman.

## Yang Sengaja Belum Dibangun

- Integrasi Finpay (`PaymentGatewayTransaction`, `WebhookLogs`).
- Download PDF invoice.
- Endpoint "Bayar Sekarang" & endpoint ringkasan bulk semua anak di API
  parent.
- Halaman React sisi orang tua (baru endpoint API yang dibangun).
- Birokrasi approval tarif khusus berjenjang (Komite Tarif & Beasiswa) —
  saat ini cuma isi manual `approved_by`+`note`.
- Test otomatis (Pest) — utang di semua modul, bukan cuma Finance.

## Yang Perlu Dilakukan Manual

- Daftarkan `FinanceModuleServiceProvider` ke `bootstrap/providers.php`
  (kalau belum).
- Seed permission `finance.manage`.
- `npm install @alpinejs/collapse` + daftarkan `Alpine.plugin(collapse)`
  di `resources/js/app.js` (dipakai buat animasi accordion sidebar &
  beberapa form collapse di modul ini).