@extends('layouts.staff')

@section('title', 'Pemetaan Tarif Siswa')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Pemetaan Tarif Siswa</h1>
            <p class="text-sm text-slate-500">Tarif yang berlaku untuk tiap siswa, termasuk diskon/tarif khusus yang disetujui.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.student-tariff-mappings.bulk-create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <i class="ti ti-users-group"></i> Pemetaan Massal
            </a>
            <a href="{{ route('finance.student-tariff-mappings.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
                <i class="ti ti-plus"></i> Tambah Pemetaan
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
                    <th class="pb-3 font-medium">Siswa</th>
                    <th class="pb-3 font-medium">Tarif</th>
                    <th class="pb-3 font-medium">Tahun Ajaran</th>
                    <th class="pb-3 font-medium">Disetujui Oleh</th>
                    <th class="pb-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($studentTariffMappings as $mapping)
                    <tr class="border-b border-slate-50 align-top">
                        <td class="py-3 text-slate-700">{{ $mapping->student->full_name }}</td>
                        <td class="py-3">
                            <div class="text-slate-700">{{ $mapping->billingTariff->tariff_name }}</div>
                            <div class="text-xs text-slate-400">Rp{{ number_format($mapping->billingTariff->amount, 0, ',', '.') }}</div>
                            @if ($mapping->note)
                                <div class="mt-1 text-xs text-amber-600">{{ $mapping->note }}</div>
                            @endif
                        </td>
                        <td class="py-3 text-slate-500">{{ $mapping->academicYear->year_name }}</td>
                        <td class="py-3">
                            @if ($mapping->approvedBy)
                                <x-status-badge status="success">{{ $mapping->approvedBy->email }}</x-status-badge>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Tarif Standar</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('finance.student-tariff-mappings.edit', $mapping) }}"
                                   class="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
                                <form action="{{ route('finance.student-tariff-mappings.destroy', $mapping) }}" method="POST"
                                      onsubmit="return confirm('Hapus pemetaan tarif ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada pemetaan tarif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $studentTariffMappings->links() }}
        </div>
    </div>
</div>
@endsection