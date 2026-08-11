@extends('layouts.staff')

@section('title', 'Rekap SPP per Bulan')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('finance.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Laporan Keuangan</a>
        <h1 class="mt-1 text-xl font-semibold text-slate-800">Rekap SPP per Bulan</h1>
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
            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Tampilkan</button>
        </div>
    </form>

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">Periode</th>
                    <th class="pb-3 font-medium text-right">Jumlah Invoice</th>
                    <th class="pb-3 font-medium text-right">Total Tagihan</th>
                    <th class="pb-3 font-medium text-right">Total Terbayar</th>
                    <th class="pb-3 font-medium text-right">Tunggakan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                @endphp
                @forelse ($recap as $row)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $bulanIndo[$row['period_month']] }} {{ $row['period_year'] }}</td>
                        <td class="py-3 text-right text-slate-500">{{ $row['jumlah_invoice'] }}</td>
                        <td class="py-3 text-right text-slate-700">Rp{{ number_format($row['total_tagihan'], 0, ',', '.') }}</td>
                        <td class="py-3 text-right text-emerald-600">Rp{{ number_format($row['total_terbayar'], 0, ',', '.') }}</td>
                        <td class="py-3 text-right text-rose-600">Rp{{ number_format($row['total_tunggakan'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">Belum ada data untuk tahun ajaran ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection