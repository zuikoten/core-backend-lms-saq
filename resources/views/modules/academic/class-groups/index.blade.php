@extends('layouts.staff')

@section('title', 'Rombel')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Rombel</h1>
            <p class="text-sm text-slate-500">Kelas/rombongan belajar per tahun ajaran.</p>
        </div>
        <a href="{{ route('class-groups.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Tambah Rombel
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
                    <th class="px-4 py-3 font-medium">Rombel</th>
                    <th class="px-4 py-3 font-medium">Tingkat</th>
                    <th class="px-4 py-3 font-medium">Tahun Ajaran</th>
                    <th class="px-4 py-3 font-medium">Ruang</th>
                    <th class="px-4 py-3 font-medium">Jml. Siswa</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($classGroups as $classGroup)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('class-groups.show', $classGroup) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $classGroup->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $classGroup->gradeLevel->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $classGroup->academicYear->year_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $classGroup->classroom->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $classGroup->active_student_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('class-groups.edit', $classGroup) }}"
                                   class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                    Edit
                                </a>
                                <form action="{{ route('class-groups.destroy', $classGroup) }}" method="POST"
                                      onsubmit="return confirm('Hapus rombel {{ $classGroup->name }}?');">
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
                        <td colspan="6" class="px-4 py-6 text-center text-slate-400">
                            Belum ada rombel. Tambahkan yang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
