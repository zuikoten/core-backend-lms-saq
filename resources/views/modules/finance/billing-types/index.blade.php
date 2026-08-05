@extends('layouts.staff')

@section('title', 'Jenis Tagihan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Jenis Tagihan</h1>
            <p class="text-sm text-slate-500">Master data jenis tagihan (SPP, uang gedung, dll.) yang dipakai di tarif & invoice.</p>
        </div>
        <a href="{{ route('finance.billing-types.create') }}"
           class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">
            <i class="ti ti-plus"></i> Tambah Jenis Tagihan
        </a>
    </div>

    @if (session('status'))
        <div class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl bg-white p-6 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-left text-slate-500">
                    <th class="pb-3 font-medium">Nama</th>
                    <th class="pb-3 font-medium">Tipe</th>
                    <th class="pb-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($billingTypes as $billingType)
                    <tr class="border-b border-slate-50">
                        <td class="py-3 text-slate-700">{{ $billingType->name }}</td>
                        <td class="py-3">
                            @if ($billingType->is_recurring)
                                <x-status-badge status="success">Berulang / Bulanan</x-status-badge>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Sekali Bayar</span>
                            @endif
                        </td>
                        <td class="py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('finance.billing-types.edit', $billingType) }}"
                                   class="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Edit</a>
                                <form action="{{ route('finance.billing-types.destroy', $billingType) }}" method="POST"
                                      onsubmit="return confirm('Hapus jenis tagihan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-slate-400">Belum ada jenis tagihan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $billingTypes->links() }}
        </div>
    </div>
</div>
@endsection
