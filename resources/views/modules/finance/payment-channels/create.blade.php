@extends('layouts.staff')

@section('title', 'Tambah Kanal Pembayaran')

@section('content')
<div class="max-w-xl">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Tambah Kanal Pembayaran</h1>

    <form action="{{ route('finance.payment-channels.store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Kanal</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                   placeholder="Contoh: BCA - Rekening Sekolah">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Kanal</label>
            <select name="channel_type"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="bank_transfer" @selected(old('channel_type') == 'bank_transfer')>Transfer Bank</option>
                <option value="virtual_account" @selected(old('channel_type') == 'virtual_account')>Virtual Account</option>
                <option value="e_wallet" @selected(old('channel_type') == 'e_wallet')>E-Wallet</option>
                <option value="cash" @selected(old('channel_type') == 'cash')>Tunai</option>
            </select>
            @error('channel_type')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor Rekening</label>
                <input type="text" name="account_number" value="{{ old('account_number') }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                       placeholder="Opsional, kosongkan untuk Tunai">
                @error('account_number')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Atas Nama</label>
                <input type="text" name="account_holder_name" value="{{ old('account_holder_name') }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                       placeholder="Opsional">
                @error('account_holder_name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Provider</label>
            <select name="provider"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="manual" @selected(old('provider', 'manual') == 'manual')>Manual (dicatat sendiri oleh kasir)</option>
                <option value="finpay" @selected(old('provider') == 'finpay')>Finpay (payment gateway)</option>
            </select>
            <p class="mt-1 text-xs text-slate-400">Integrasi Finpay masih dalam tahap penjajakan — pilih Manual untuk kanal yang dicatat manual oleh staf.</p>
            @error('provider')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kode Kanal Provider</label>
            <input type="text" name="provider_channel_code" value="{{ old('provider_channel_code') }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400"
                   placeholder="Opsional, diisi kalau provider = Finpay">
            @error('provider_channel_code')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
            <select name="is_active"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="1" @selected(old('is_active', '1') == '1')>Aktif</option>
                <option value="0" @selected(old('is_active') == '0')>Nonaktif</option>
            </select>
            @error('is_active')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
            <a href="{{ route('finance.payment-channels.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection
