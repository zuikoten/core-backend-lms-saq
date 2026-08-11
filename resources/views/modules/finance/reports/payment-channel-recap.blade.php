@extends('layouts.staff')

@section('title', 'Rekap per Kanal Pembayaran')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('finance.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Laporan Keuangan</a>
        <h1 class="mt-1 text-xl font-semibold text-slate-800">Rekap per Kanal Pembayaran</h1>
    </div>

    <form method="GET" class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Dari Tanggal</label>
                <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
            <div class="flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Sampai Tanggal</label>
                <input type="date" name="date_to" value="{{ $dateTo }}" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
        </div>
        <p class="mt-2 text-xs text-slate-400">Kosongkan tanggal untuk melihat rekap sepanjang waktu.</p>
    </form>

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">Kanal</th>
                    <th class="pb-3 font-medium">Tipe</th>
                    <th class="pb-3 font-medium text-right">Jumlah Transaksi</th>
                    <th class="pb-3 font-medium text-right">Total Diterima</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recap as $row)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $row->name }}</td>
                        <td class="py-3 text-slate-500">{{ str($row->channel_type)->headline() }}</td>
                        <td class="py-3 text-right text-slate-500">{{ $row->jumlah_transaksi }}</td>
                        <td class="py-3 text-right text-slate-700">Rp{{ number_format($row->total_diterima, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-6 text-center text-slate-400">Belum ada pembayaran pada rentang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection