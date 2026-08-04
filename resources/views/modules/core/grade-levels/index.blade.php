@extends('layouts.staff')

@section('title', 'Tingkat / Grade Level')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tingkat / Grade Level</h1>
            <p class="text-sm text-slate-500">Tingkat di dalam tiap jenjang (mis. TK-A, TK-B).</p>
        </div>
        <a href="{{ route('grade-levels.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Tambah Tingkat
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Jenjang</th>
                    <th class="px-4 py-3 font-medium">Nama Tingkat</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($gradeLevels as $gradeLevel)
                    <tr>
                        <td class="px-4 py-3 text-slate-500">{{ $gradeLevel->jenjang->name }}</td>
                        <td class="px-4 py-3 text-slate-700 font-medium">{{ $gradeLevel->name }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('grade-levels.edit', $gradeLevel) }}"
                                   class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                    Edit
                                </a>
                                <form action="{{ route('grade-levels.destroy', $gradeLevel) }}" method="POST"
                                      onsubmit="return confirm('Hapus tingkat {{ $gradeLevel->name }}?');">
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
                            Belum ada tingkat. Tambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
