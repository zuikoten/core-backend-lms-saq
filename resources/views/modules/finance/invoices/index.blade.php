@extends('layouts.staff')

@section('title', 'Invoice')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Invoice</h1>
            <p class="text-sm text-slate-500">Tagihan bulanan siswa — SPP (generate massal) & tagihan lain (manual).</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.invoices.manual-create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="ti ti-plus"></i> Invoice Manual
            </a>
            <a href="{{ route('finance.invoices.bulk-create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                <i class="ti ti-receipt"></i> Generate SPP Bulanan
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">No. Invoice</th>
                    <th class="pb-3 font-medium">Siswa</th>
                    <th class="pb-3 font-medium">Periode</th>
                    <th class="pb-3 font-medium">Total</th>
                    <th class="pb-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $bulanIndo = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];
                    $statusMap = ['unpaid' => 'danger', 'partial' => 'warning', 'paid' => 'success', 'cancelled' => null];
                @endphp
                @forelse ($invoices as $invoice)
                    <tr class="border-b border-slate-50 cursor-pointer hover:bg-slate-50" onclick="window.location='{{ route('finance.invoices.show', $invoice) }}'">
                        <td class="py-3 text-slate-700">{{ $invoice->invoice_number }}</td>
                        <td class="py-3 text-slate-700">{{ $invoice->student->full_name }}</td>
                        <td class="py-3 text-slate-500">{{ $bulanIndo[$invoice->period_month] }} {{ $invoice->period_year }}</td>
                        <td class="py-3 text-slate-700">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        <td class="py-3">
                            @if ($statusMap[$invoice->status])
                                <x-status-badge :status="$statusMap[$invoice->status]">{{ ucfirst($invoice->status) }}</x-status-badge>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Dibatalkan</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada invoice.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection