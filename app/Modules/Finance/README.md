# Modul Finance

Pembayaran SPP & tagihan lain. Dibangun bertahap sesuai urutan dependency
skema: **BillingType & PaymentChannel** (master data) → BillingTariff →
StudentTariffMapping → Invoice & InvoiceItem → InvoicePayment →
PaymentGatewayTransaction & WebhookLogs (integrasi Finpay, ditunda sampai
provider final).

Permission: `finance.manage` — satu permission menaungi seluruh entitas
modul ini (pola sama dengan `core.manage`), belum ada pemisahan permission
per entitas.

## Status

### ✅ BillingType — CRUD selesai
Master data jenis tagihan (mis. "SPP Bulanan", "Uang Pangkal").
`is_recurring` membedakan tagihan berulang (bulanan) vs sekali bayar.
Guard hapus: dicek lewat `DB::table('billing_tariffs')` dan
`DB::table('invoice_items')` (bukan Model, karena BillingTariff &
InvoiceItem belum dibuat) — tidak bisa dihapus kalau sudah dipakai di
salah satunya.

### ✅ PaymentChannel — CRUD selesai
Master data kanal pembayaran (transfer bank, VA, e-wallet, tunai).
`provider` membedakan kanal manual (dicatat kasir) vs `finpay` (payment
gateway — belum diintegrasikan). Guard hapus: dicek lewat
`DB::table('invoice_payments')` dan `DB::table('payment_gateway_transactions')`.
Kanal yang sudah tidak dipakai tapi ingin disembunyikan dari pilihan baru
cukup dinonaktifkan lewat `is_active`, tidak perlu dihapus.

### ⏳ Belum digarap
- **BillingTariff** — tarif per jenis tagihan per tahun ajaran.
- **StudentTariffMapping** — pemetaan tarif ke siswa (termasuk diskon/
  beasiswa lewat `note` & `approved_by`).
- **Invoice & InvoiceItem** — tagihan bulanan per siswa. `invoices` unik
  per `(student_id, academic_year_id, period_month)` — **tidak** pakai
  `semester_id` meski tabel `semesters` ada (itu dipakai Rapor, bukan
  Finance). `due_date` nullable karena ada skema tanpa tenggat jelas
  (tabungan sukarela, dll).
- **InvoicePayment** — pencatatan pembayaran manual (transfer/tunai) oleh
  kasir, lewat `handover_by`.
- **PaymentGatewayTransaction & WebhookLogs** — integrasi Finpay, ditunda
  sampai provider final. Skema tabelnya sudah ada dari awal project.

## Catatan Desain

- Route & view saat ini staf-only (Blade, guard `web`). Belum ada `api.php`
  untuk orang tua — akan ditambahkan begitu modul sampai ke Invoice
  (orang tua perlu lihat tagihan anak, read-only, di-scope dari
  `ParentProfile`).
- View pakai layout `layouts.staff`, component `x-status-badge` (tanpa
  varian netral — status "Nonaktif"/"Sekali Bayar" pakai `<span>` manual).

## Yang Perlu Dilakukan Manual

- Daftarkan `FinanceModuleServiceProvider` ke `bootstrap/providers.php`.
- Seed permission `finance.manage` (lewat `PermissionSeeder` atau Tinker).
