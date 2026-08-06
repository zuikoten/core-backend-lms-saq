@extends('layouts.staff')

@section('title', 'Pemetaan Tarif Massal')

@section('content')
<div class="max-w-2xl" x-data="{ filterType: 'all' }">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Pemetaan Tarif Massal</h1>
    <p class="mb-6 text-sm text-slate-500">Pilih tarif & cakupan siswa, langkah berikutnya kamu bisa cek & centang daftar siswanya sebelum benar-benar disimpan.</p>

    <form action="{{ route('finance.student-tariff-mappings.bulk-preview') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tarif</label>
            <select name="billing_tariff_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih Tarif —</option>
                @foreach ($billingTariffs as $billingTariff)
                    <option value="{{ $billingTariff->id }}" @selected(old('billing_tariff_id') == $billingTariff->id)>
                        {{ $billingTariff->billingType->name }} — {{ $billingTariff->tariff_name }} ({{ $billingTariff->academicYear->year_name }}) — Rp{{ number_format($billingTariff->amount, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
            @error('billing_tariff_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Cakupan Siswa</label>
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="radio" name="filter_type" value="all" x-model="filterType" checked>
                    Semua siswa aktif di tahun ajaran tarif ini
                </label>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="radio" name="filter_type" value="class_group" x-model="filterType">
                    Rombel tertentu
                </label>
            </div>
            @error('filter_type')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4" x-show="filterType === 'class_group'" x-collapse>
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Rombel</label>
            <select name="class_group_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Pilih Rombel —</option>
                @foreach ($classGroups as $classGroup)
                    <option value="{{ $classGroup->id }}" @selected(old('class_group_id') == $classGroup->id)>
                        {{ $classGroup->name }} — {{ $classGroup->gradeLevel->name }} ({{ $classGroup->academicYear->year_name }})
                    </option>
                @endforeach
            </select>
            @error('class_group_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Disetujui Oleh (opsional)</label>
            <select name="approved_by"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Tarif Standar, Tanpa Persetujuan —</option>
                @foreach ($kepalaSekolahOptions as $user)
                    <option value="{{ $user->id }}" @selected(old('approved_by') == $user->id)>{{ $user->email }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-400">Isi kalau seluruh cakupan ini mendapat tarif khusus yang sama (mis. diskon rombel ABK). Kosongkan untuk tarif standar.</p>
            @error('approved_by')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>
            <textarea name="note" rows="2"
                      class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                      placeholder="Wajib diisi kalau ada persetujuan">{{ old('note') }}</textarea>
            @error('note')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Lihat & Cek Daftar Siswa</button>
            <a href="{{ route('finance.student-tariff-mappings.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection