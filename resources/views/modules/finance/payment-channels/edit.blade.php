@extends('layouts.staff')

@section('title', 'Edit Kanal Pembayaran')

@section('content')
<div class="max-w-xl">
    <h1 class="mb-6 text-xl font-semibold text-slate-800">Edit Kanal Pembayaran</h1>

    <form action="{{ route('finance.payment-channels.update', $paymentChannel) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Kanal</label>
            <input type="text" name="name" value="{{ old('name', $paymentChannel->name) }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('name')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Kanal</label>
            <select name="channel_type"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @foreach (['bank_transfer' => 'Transfer Bank', 'virtual_account' => 'Virtual Account', 'e_wallet' => 'E-Wallet', 'cash' => 'Tunai'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('channel_type', $paymentChannel->channel_type) == $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('channel_type')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nomor Rekening</label>
                <input type="text" name="account_number" value="{{ old('account_number', $paymentChannel->account_number) }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('account_number')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Atas Nama</label>
                <input type="text" name="account_holder_name" value="{{ old('account_holder_name', $paymentChannel->account_holder_name) }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('account_holder_name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Provider</label>
            <select name="provider"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="manual" @selected(old('provider', $paymentChannel->provider) == 'manual')>Manual (dicatat sendiri oleh kasir)</option>
                <option value="finpay" @selected(old('provider', $paymentChannel->provider) == 'finpay')>Finpay (payment gateway)</option>
            </select>
            @error('provider')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Kode Kanal Provider</label>
            <input type="text" name="provider_channel_code" value="{{ old('provider_channel_code', $paymentChannel->provider_channel_code) }}"
                   class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            @error('provider_channel_code')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
            <select name="is_active"
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="1" @selected(old('is_active', $paymentChannel->is_active ? '1' : '0') == '1')>Aktif</option>
                <option value="0" @selected(old('is_active', $paymentChannel->is_active ? '1' : '0') == '0')>Nonaktif</option>
            </select>
            @error('is_active')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Perubahan</button>
            <a href="{{ route('finance.payment-channels.index') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Batal</a>
        </div>
    </form>
</div>
@endsection
