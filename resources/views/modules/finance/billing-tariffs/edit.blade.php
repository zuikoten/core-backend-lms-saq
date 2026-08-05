@extends('layouts.staff')

@section('title', 'Edit Tarif')

@section('content')
<div class="max-w-xl">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Edit Tarif</h1>

    <form action="{{ route('finance.billing-tariffs.update', $billingTariff) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Tagihan</label>
            <select name="billing_type_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @foreach ($billingTypes as $billingType)
                    <option value="{{ $billingType->id }}" @selected(old('billing_type_id', $billingTariff->billing_type_id) == $billingType->id)>{{ $billingType->name }}</option>
                @endforeach
            </select>
            @error('billing_type_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
            <select name="academic_year_id"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @foreach ($academicYears as $academicYear)
                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id', $billingTariff->academic_year_id) == $academicYear->id)>{{ $academicYear->year_name }}</option>
                @endforeach
            </select>
            @error('academic_year_id')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Tarif</label>
            <input type="text" name="tariff_name" value="{{ old('tariff_name', $billingTariff->tariff_name) }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('tariff_name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nominal (Rp)</label>
            <input type="number" name="amount" value="{{ old('amount', $billingTariff->amount) }}" step="0.01" min="0"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('amount')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
            <a href="{{ route('finance.billing-tariffs.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection