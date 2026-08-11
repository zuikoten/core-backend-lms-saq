@extends('layouts.staff')

@section('title', 'Daftar Tunggakan')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('finance.reports.index') }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Laporan Keuangan</a>
        <h1 class="mt-1 text-xl font-semibold text-slate-800">Daftar Tunggakan</h1>
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
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Rombel (opsional)</label>
                <select name="class_group_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">Semua Rombel</option>
                    @foreach ($classGroups as $classGroup)
                        <option value="{{ $classGroup->id }}" @selected($classGroupId == $classGroup->id)>{{ $classGroup->name }} — {{ $classGroup->gradeLevel->name }} ({{ $classGroup->academicYear->year_name }})</option>
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
                    <th class="pb-3 font-medium">Siswa</th>
                    <th class="pb-3 font-medium">No. Invoice</th>
                    <th class="pb-3 font-medium">Jatuh Tempo</th>
                    <th class="pb-3 font-medium text-right">Sisa Tagihan</th>
                    <th class="pb-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($outstandingInvoices as $row)
                    <tr class="cursor-pointer border-b border-slate-50 hover:bg-slate-50" onclick="window.location='{{ route('finance.invoices.show', $row['invoice']) }}'">
                        <td class="py-3 text-slate-700">{{ $row['invoice']->student->full_name }}</td>
                        <td class="py-3 text-slate-500">{{ $row['invoice']->invoice_number }}</td>
                        <td class="py-3 text-slate-500">{{ $row['invoice']->due_date?->translatedFormat('d M Y') ?? '—' }}</td>
                        <td class="py-3 text-right text-rose-600">Rp{{ number_format($row['sisa_tagihan'], 0, ',', '.') }}</td>
                        <td class="py-3">
                            <x-status-badge :status="$row['invoice']->status === 'partial' ? 'warning' : 'danger'">{{ ucfirst($row['invoice']->status) }}</x-status-badge>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">Tidak ada tunggakan — semua invoice lunas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection