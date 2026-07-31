{{-- resources/views/modules/core/academic-years/index.blade.php --}}
@extends('layouts.staff')

@section('title', 'Tahun Ajaran')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tahun Ajaran</h1>
            <p class="text-sm text-slate-500">Kelola periode tahun ajaran & tentukan yang sedang aktif.</p>
        </div>
        <a href="{{ route('academic-years.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Tambah Tahun Ajaran
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Tahun Ajaran</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($academicYears as $academicYear)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ $academicYear->year_name }}</td>
                        <td class="px-4 py-3">
                            @if ($academicYear->is_active)
                                <x-status-badge status="success">Aktif</x-status-badge>
                            @else
                                {{-- Sengaja bukan x-status-badge: komponen itu belum
                                     punya varian netral/abu-abu, cuma hijau/kuning/merah.
                                     "Tidak aktif" bukan kondisi bermasalah. --}}
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">
                                    Tidak Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @unless ($academicYear->is_active)
                                    <form action="{{ route('academic-years.activate', $academicYear) }}" method="POST"
                                          onsubmit="return confirm('Aktifkan {{ $academicYear->year_name }}? Tahun ajaran lain yang sedang aktif akan otomatis dinonaktifkan.');">
                                        @csrf
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                            Aktifkan
                                        </button>
                                    </form>
                                @endunless

                                <a href="{{ route('academic-years.edit', $academicYear) }}"
                                   class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                    Edit
                                </a>

                                @unless ($academicYear->is_active)
                                    <form action="{{ route('academic-years.destroy', $academicYear) }}" method="POST"
                                          onsubmit="return confirm('Hapus tahun ajaran {{ $academicYear->year_name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                            Hapus
                                        </button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-slate-400">
                            Belum ada tahun ajaran. Tambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection