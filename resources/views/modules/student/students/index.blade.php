@extends('layouts.staff')

@section('title', 'Data Siswa')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Data Siswa</h1>
            <p class="text-sm text-slate-500">Kelola data siswa & orang tua/wali.</p>
        </div>
        <a href="{{ route('students.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Tambah Siswa
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
                    <th class="px-4 py-3 font-medium">Siswa</th>
                    <th class="px-4 py-3 font-medium">NISN</th>
                    <th class="px-4 py-3 font-medium">Orang Tua</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($students as $student)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <x-icon-badge :icon="$student->gender === 'L' ? 'gender-male' : 'gender-female'"
                                              :color="$student->gender === 'L' ? 'blue' : 'indigo'" />
                                <div>
                                    <p class="font-medium text-slate-700">{{ $student->full_name }}</p>
                                    @if ($student->nickname)
                                        <p class="text-xs text-slate-400">{{ $student->nickname }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $student->nisn ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">
                            {{ $student->parentProfile->father_name ?? $student->parentProfile->mother_name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @switch($student->status)
                                @case('aktif')
                                    <x-status-badge status="success">Aktif</x-status-badge>
                                    @break
                                @case('mutasi')
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600">Mutasi</span>
                                    @break
                                @case('lulus')
                                    <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">Lulus</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('students.edit', $student) }}"
                                   class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                    Edit
                                </a>
                                <form action="{{ route('students.destroy', $student) }}" method="POST"
                                      onsubmit="return confirm('Hapus data siswa {{ $student->full_name }}?');">
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
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">
                            Belum ada data siswa. Tambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection