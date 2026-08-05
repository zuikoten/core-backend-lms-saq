@extends('layouts.staff')

@section('title', 'Edit Jenis Tagihan')

@section('content')
<div class="max-w-xl">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Edit Jenis Tagihan</h1>

    <form action="{{ route('finance.billing-types.update', $billingType) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Jenis Tagihan</label>
            <input type="text" name="name" value="{{ old('name', $billingType->name) }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Tagihan</label>
            <select name="is_recurring"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="1" @selected(old('is_recurring', $billingType->is_recurring ? '1' : '0') == '1')>Berulang / Bulanan (mis. SPP)</option>
                <option value="0" @selected(old('is_recurring', $billingType->is_recurring ? '1' : '0') == '0')>Sekali Bayar (mis. Uang Pangkal)</option>
            </select>
            @error('is_recurring')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
            <a href="{{ route('finance.billing-types.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection
