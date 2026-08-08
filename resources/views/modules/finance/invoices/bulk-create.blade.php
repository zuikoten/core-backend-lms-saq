@extends('layouts.staff')

@section('title', 'Generate SPP Bulanan')

@section('content')
<div class="max-w-2xl" x-data="bulkInvoiceGeneration()">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Generate SPP Bulanan</h1>
    <p class="mb-6 text-sm text-slate-500">Siswa yang muncul di bawah cuma yang punya tarif recurring & belum kena invoice periode ini.</p>

    <form action="{{ route('finance.invoices.bulk-store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
                <select name="academic_year_id" x-model="academicYearId" @change="fetchStudents()"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->year_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Jatuh Tempo (opsional)</label>
                <input type="date" name="due_date" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Bulan</label>
                <select name="period_month" x-model="periodMonth" @change="fetchStudents()"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @foreach (['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun</label>
                <input type="number" name="period_year" x-model="periodYear" @change="fetchStudents()" min="2020" max="2100"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
        </div>

        <div class="mb-3 border-t border-slate-100 pt-4">
            <template x-if="!academicYearId">
                <p class="py-6 text-center text-sm text-slate-400">Pilih tahun ajaran dulu untuk menampilkan daftar siswa.</p>
            </template>

            <template x-if="academicYearId && loading">
                <p class="py-6 text-center text-sm text-slate-400">Memuat daftar siswa...</p>
            </template>

            <template x-if="academicYearId && !loading && students.length === 0">
                <p class="py-6 text-center text-sm text-slate-400">Tidak ada siswa yang perlu di-invoice untuk periode ini.</p>
            </template>

            <template x-if="academicYearId && !loading && students.length > 0">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" :checked="selectedIds.length === students.length" @change="toggleAll($event.target.checked)">
                        <span x-text="'Pilih Semua (' + students.length + ' siswa)'"></span>
                    </label>

                    <div class="max-h-96 space-y-1 overflow-y-auto border-y border-slate-100 py-2">
                        <template x-for="student in students" :key="student.id">
                            <label class="flex items-center justify-between gap-2 rounded-lg px-2 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" name="student_ids[]" :value="student.id" x-model="selectedIds">
                                    <span x-text="student.full_name"></span>
                                </span>
                                <span class="text-slate-400" x-text="'Rp' + student.total_amount.toLocaleString('id-ID')"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex gap-3">
            <button type="submit" :disabled="selectedIds.length === 0"
                    class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                Generate Invoice <span x-show="selectedIds.length > 0" x-text="'(' + selectedIds.length + ' siswa)'"></span>
            </button>
            <a href="{{ route('finance.invoices.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.financeRoutes = window.financeRoutes || {};
        window.financeRoutes.eligibleInvoiceStudents = "{{ route('finance.invoices.eligible-students') }}";
    </script>
    @vite(['resources/js/modules/finance/invoice-bulk-create.js'])
@endpush
@endsection