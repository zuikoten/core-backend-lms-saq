@extends('layouts.staff')

@section('title', 'Breakdown Komponen Biaya')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('finance.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Laporan Keuangan</a>
        <h1 class="mt-1 text-xl font-semibold text-slate-800">Breakdown Komponen Biaya</h1>
        <p class="text-sm text-slate-500">Uang masuk dipisah per komponen biaya. Alokasi memakai aturan FIFO — SPP diprioritaskan lunas duluan.</p>
    </div>

    <form method="GET" class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected($academicYearId == $academicYear->id)>{{ $academicYear->year_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Bulan (opsional)</label>
                <select name="period_month" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">Semua Bulan</option>
                    @foreach (['1'=>'Januari','2'=>'Februari','3'=>'Maret','4'=>'April','5'=>'Mei','6'=>'Juni','7'=>'Juli','8'=>'Agustus','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'] as $value => $label)
                        <option value="{{ $value }}" @selected($periodMonth == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
        </div>
    </form>

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">Komponen Biaya</th>
                    <th class="pb-3 font-medium text-right">Total Diterima</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($breakdown as $row)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $row['billing_type_name'] }}</td>
                        <td class="py-3 text-right text-slate-700">Rp{{ number_format($row['total_diterima'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="py-6 text-center text-slate-400">Belum ada pembayaran pada periode ini.</td></tr>
                @endforelse
            </tbody>
            @if ($breakdown->isNotEmpty())
                <tfoot>
                    <tr>
                        <td class="pt-3 font-semibold text-slate-800">Total</td>
                        <td class="pt-3 text-right font-semibold text-slate-800">Rp{{ number_format($breakdown->sum('total_diterima'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection