@extends('layouts.staff')

@section('title', 'Pemetaan Tarif Massal')

@section('content')
<div class="max-w-2xl" x-data="bulkTariffMapping()">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Pemetaan Tarif Massal</h1>
    <p class="mb-6 text-sm text-slate-500">Pilih tarif & cakupan siswa, daftar siswa di bawah otomatis ter-update — centang siswa yang ingin dipetakan, lalu simpan.</p>

    <form action="{{ route('finance.student-tariff-mappings.bulk-store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tarif</label>
            <select name="billing_tariff_id" x-model="billingTariffId" @change="fetchStudents()"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih Tarif —</option>
                @foreach ($billingTariffs as $billingTariff)
                    <option value="{{ $billingTariff->id }}">
                        {{ $billingTariff->billingType->name }} — {{ $billingTariff->tariff_name }} ({{ $billingTariff->academicYear->year_name }}) — Rp{{ number_format($billingTariff->amount, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Cakupan Siswa</label>
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="radio" value="all" x-model="filterType" @change="fetchStudents()">
                    Semua siswa aktif di tahun ajaran tarif ini
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="radio" value="class_group" x-model="filterType" @change="fetchStudents()">
                    Rombel tertentu
                </label>
            </div>
        </div>

        <div class="mb-4" x-show="filterType === 'class_group'" x-collapse>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Rombel</label>
            <select x-model="classGroupId" @change="fetchStudents()"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih Rombel —</option>
                @foreach ($classGroups as $classGroup)
                    <option value="{{ $classGroup->id }}">
                        {{ $classGroup->name }} — {{ $classGroup->gradeLevel->name }} ({{ $classGroup->academicYear->year_name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Disetujui Oleh (opsional)</label>
            <select name="approved_by"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Tarif Standar, Tanpa Persetujuan —</option>
                @foreach ($kepalaSekolahOptions as $user)
                    <option value="{{ $user->id }}">{{ $user->email }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-400">Isi kalau seluruh cakupan ini mendapat tarif khusus yang sama. Kosongkan untuk tarif standar.</p>
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>
            <textarea name="note" rows="2"
                      class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                      placeholder="Wajib diisi kalau ada persetujuan"></textarea>
        </div>

        <div class="mb-3 border-t border-slate-100 pt-4">
            <template x-if="!billingTariffId">
                <p class="py-6 text-center text-sm text-slate-400">Pilih tarif dulu untuk menampilkan daftar siswa.</p>
            </template>

            <template x-if="billingTariffId && loading">
                <p class="py-6 text-center text-sm text-slate-400">Memuat daftar siswa...</p>
            </template>

            <template x-if="billingTariffId && !loading && students.length === 0">
                <p class="py-6 text-center text-sm text-slate-400">Tidak ada siswa yang cocok — kemungkinan semua siswa di cakupan ini sudah punya pemetaan tarif untuk jenis tagihan yang sama.</p>
            </template>

            <template x-if="billingTariffId && !loading && students.length > 0">
                <div>
                    <label class="mb-2 flex items-center gap-2 text-sm font-medium text-slate-700">
                        <input type="checkbox" :checked="selectedIds.length === students.length" @change="toggleAll($event.target.checked)">
                        <span x-text="'Pilih Semua (' + students.length + ' siswa)'"></span>
                    </label>

                    <div class="max-h-96 space-y-1 overflow-y-auto border-y border-slate-100 py-2">
                        <template x-for="student in students" :key="student.id">
                            <label class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-slate-600 hover:bg-slate-50">
                                <input type="checkbox" name="student_ids[]" :value="student.id" x-model="selectedIds">
                                <span x-text="student.full_name"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex gap-3">
            <button type="submit" :disabled="selectedIds.length === 0"
                    class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                Simpan Pemetaan <span x-show="selectedIds.length > 0" x-text="'(' + selectedIds.length + ' siswa)'"></span>
            </button>
            <a href="{{ route('finance.student-tariff-mappings.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        window.financeRoutes = window.financeRoutes || {};
        window.financeRoutes.eligibleStudentTariffs = "{{ route('finance.student-tariff-mappings.eligible-students') }}";
    </script>
    @vite(['resources/js/modules/finance/student-tariff-mapping-bulk-create.js'])
@endpush
@endsection