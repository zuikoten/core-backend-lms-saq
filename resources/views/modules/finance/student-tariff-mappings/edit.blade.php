@extends('layouts.staff')

@section('title', 'Edit Pemetaan Tarif')

@section('content')
<div class="max-w-xl">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Edit Pemetaan Tarif</h1>

    <form action="{{ route('finance.student-tariff-mappings.update', $studentTariffMapping) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Siswa</label>
            <select name="student_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $studentTariffMapping->student_id) == $student->id)>{{ $student->full_name }}</option>
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tarif</label>
            <select name="billing_tariff_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @foreach ($billingTariffs as $billingTariff)
                    <option value="{{ $billingTariff->id }}" @selected(old('billing_tariff_id', $studentTariffMapping->billing_tariff_id) == $billingTariff->id)>
                        {{ $billingTariff->billingType->name }} — {{ $billingTariff->tariff_name }} ({{ $billingTariff->academicYear->year_name }}) — Rp{{ number_format($billingTariff->amount, 0, ',', '.') }}
                    </option>
                @endforeach
            </select>
            @error('billing_tariff_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Disetujui Oleh (opsional)</label>
            <select name="approved_by"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">— Tarif Standar, Tanpa Persetujuan —</option>
                @foreach ($kepalaSekolahOptions as $user)
                    <option value="{{ $user->id }}" @selected(old('approved_by', $studentTariffMapping->approved_by) == $user->id)>{{ $user->email }}</option>
                @endforeach
            </select>
            @error('approved_by')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Catatan</label>
            <textarea name="note" rows="3"
                      class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('note', $studentTariffMapping->note) }}</textarea>
            @error('note')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
            <a href="{{ route('finance.student-tariff-mappings.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection