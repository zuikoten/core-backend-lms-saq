@extends('layouts.staff')

@section('title', 'Detail Invoice')

@section('content')
@php
    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $statusMap = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success', 'cancelled' => null];
@endphp
<div class="max-w-2xl space-y-6">
    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">{{ $invoice->invoice_number }}</h1>
            <p class="text-sm text-slate-500">{{ $invoice->student->full_name }} — {{ $bulanIndo[$invoice->period_month] }} {{ $invoice->period_year }}</p>
        </div>
        @if ($statusMap[$invoice->status])
            <x-status-badge :status="$statusMap[$invoice->status]">{{ ucfirst($invoice->status) }}</x-status-badge>
        @else
            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Dibatalkan</span>
        @endif
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <div class="mb-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-slate-400">Tahun Ajaran</p>
                <p class="text-slate-700">{{ $invoice->academicYear->year_name }}</p>
            </div>
            <div>
                <p class="text-slate-400">Jatuh Tempo</p>
                <p class="text-slate-700">{{ $invoice->due_date?->translatedFormat('d M Y') ?? '—' }}</p>
            </div>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">Item</th>
                    <th class="pb-3 font-medium text-right">Nominal</th>
                    @if ($invoice->status === 'unpaid')
                        <th class="pb-3 font-medium text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $item->item_name }}</td>
                        <td class="py-3 text-right text-slate-700">Rp{{ number_format($item->amount, 0, ',', '.') }}</td>
                        @if ($invoice->status === 'unpaid')
                            <td class="py-3 text-right">
                                <form action="{{ route('finance.invoices.items.destroy', [$invoice, $item]) }}" method="POST"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="pt-3 font-semibold text-slate-800">Total</td>
                    <td class="pt-3 text-right font-semibold text-slate-800">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                    @if ($invoice->status === 'unpaid') <td></td> @endif
                </tr>
            </tfoot>
        </table>

        @if ($invoice->status === 'unpaid')
            <form action="{{ route('finance.invoices.items.store', $invoice) }}" method="POST" class="mt-6 border-t border-slate-100 pt-4">
                @csrf
                <p class="mb-3 text-sm font-medium text-slate-700">Tambah Item</p>
                <div class="grid grid-cols-3 gap-3">
                    <select name="billing_type_id" class="rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="">Jenis Tagihan</option>
                        @foreach ($billingTypes as $billingType)
                            <option value="{{ $billingType->id }}">{{ $billingType->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="item_name" placeholder="Nama Item" class="rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <input type="number" name="amount" step="0.01" min="0" placeholder="Nominal" class="rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>
                <button type="submit" class="mt-3 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Tambah</button>
            </form>

            <form action="{{ route('finance.invoices.destroy', $invoice) }}" method="POST" class="mt-4" onsubmit="return confirm('Hapus invoice ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-rose-600 hover:underline">Hapus Invoice Ini</button>
            </form>
        @endif
    </div>

    <a href="{{ route('finance.invoices.index') }}" class="inline-block text-sm text-slate-500 hover:text-slate-700">&larr; Kembali ke Daftar Invoice</a>
</div>
@endsection