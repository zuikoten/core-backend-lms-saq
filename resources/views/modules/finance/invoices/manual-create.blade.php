@extends('layouts.staff')

@section('title', 'Invoice Manual')

@section('content')
<div class="max-w-2xl" x-data="manualInvoiceForm(@js($billingTariffs->map(fn ($t) => ['id' => $t->id, 'billing_type_id' => $t->billing_type_id, 'label' => $t->billingType->name.' — '.$t->tariff_name, 'amount' => $t->amount])))">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Invoice Manual</h1>
    <p class="mb-6 text-sm text-slate-500">Untuk tagihan sekali bayar di luar SPP (Uang Pangkal, dll). Kalau siswa sudah punya invoice bulan ini, tambahkan item lewat halaman invoice-nya, bukan di sini.</p>

    <form action="{{ route('finance.invoices.manual-store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Siswa</label>
            <select name="student_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih Siswa —</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->full_name }}</option>
                @endforeach
            </select>
            @error('student_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
            <select name="academic_year_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih —</option>
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id') == $academicYear->id)>{{ $academicYear->year_name }}</option>
                @endforeach
            </select>
            @error('academic_year_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <div class="mb-6 grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Bulan</label>
                <select name="period_month" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @foreach (['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('period_month', now()->month) == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun</label>
                <input type="number" name="period_year" value="{{ old('period_year', now()->year) }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Jatuh Tempo (opsional)</label>
            <input type="date" name="due_date" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
        </div>

        <div class="mb-4 border-t border-slate-100 pt-4">
            <p class="mb-3 text-sm font-medium text-slate-700">Item Tagihan</p>

            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 grid grid-cols-12 gap-2">
                    <select :name="`items[${index}][billing_tariff_ref]`" x-model="item.billing_tariff_id" @change="fillFromTariff(index)"
                            class="col-span-4 rounded-xl border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">Pilih Tarif (opsional)</option>
                        <template x-for="tariff in billingTariffs" :key="tariff.id">
                            <option :value="tariff.id" x-text="tariff.label"></option>
                        </template>
                    </select>
                    <input type="hidden" :name="`items[${index}][billing_type_id]`" x-model="item.billing_type_id">
                    <input type="text" :name="`items[${index}][item_name]`" x-model="item.item_name" placeholder="Nama Item"
                           class="col-span-4 rounded-xl border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <input type="number" :name="`items[${index}][amount]`" x-model="item.amount" step="0.01" min="0" placeholder="Nominal"
                           class="col-span-3 rounded-xl border-slate-200 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <button type="button" @click="removeItem(index)" class="col-span-1 text-rose-500 hover:text-rose-700">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </template>

            <button type="button" @click="addItem()" class="text-sm font-medium text-indigo-600 hover:underline">+ Tambah Item</button>

            <p class="mt-3 text-sm font-semibold text-slate-800">Total: <span x-text="'Rp' + total.toLocaleString('id-ID')"></span></p>
        </div>

        @error('items')<p class="mb-4 text-xs text-rose-600">{{ $message }}</p>@enderror

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Invoice</button>
            <a href="{{ route('finance.invoices.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
    @vite(['resources/js/modules/finance/invoice-manual-create.js'])
@endpush
@endsection