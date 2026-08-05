@extends('layouts.staff')

@section('title', 'Tarif Tagihan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tarif Tagihan</h1>
            <p class="text-sm text-slate-500">Nominal tarif per jenis tagihan & tahun ajaran, jadi acuan pemetaan tarif siswa.</p>
        </div>
        <a href="{{ route('finance.billing-tariffs.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
            <i class="ti ti-plus"></i> Tambah Tarif
        </a>
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
                    <th class="pb-3 font-medium">Nama Tarif</th>
                    <th class="pb-3 font-medium">Jenis Tagihan</th>
                    <th class="pb-3 font-medium">Tahun Ajaran</th>
                    <th class="pb-3 font-medium">Nominal</th>
                    <th class="pb-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($billingTariffs as $billingTariff)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $billingTariff->tariff_name }}</td>
                        <td class="py-3 text-slate-500">{{ $billingTariff->billingType->name }}</td>
                        <td class="py-3 text-slate-500">{{ $billingTariff->academicYear->year_name }}</td>
                        <td class="py-3 text-slate-700">Rp{{ number_format($billingTariff->amount, 0, ',', '.') }}</td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('finance.billing-tariffs.edit', $billingTariff) }}"
                                   class="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
                                <form action="{{ route('finance.billing-tariffs.destroy', $billingTariff) }}" method="POST"
                                      onsubmit="return confirm('Hapus tarif ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-6 text-center text-slate-400">Belum ada tarif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $billingTariffs->links() }}
        </div>
    </div>
</div>
@endsection