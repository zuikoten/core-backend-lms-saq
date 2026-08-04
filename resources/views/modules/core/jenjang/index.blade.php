@extends('layouts.staff')

@section('title', 'Jenjang')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Jenjang</h1>
            <p class="text-sm text-slate-500">Jenjang pendidikan (mis. TK, SD) — acuan untuk Tingkat/Grade Level.</p>
        </div>
        <a href="{{ route('jenjang.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Tambah Jenjang
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
                    <th class="px-4 py-3 font-medium">Nama Jenjang</th>
                    <th class="px-4 py-3 font-medium">Jumlah Tingkat</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($jenjangList as $jenjang)
                    <tr>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ $jenjang->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $jenjang->grade_levels_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('jenjang.edit', $jenjang) }}"
                                   class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                    Edit
                                </a>
                                <form action="{{ route('jenjang.destroy', $jenjang) }}" method="POST"
                                      onsubmit="return confirm('Hapus jenjang {{ $jenjang->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-slate-400">
                            Belum ada jenjang. Tambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
