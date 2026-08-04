@extends('layouts.staff')

@section('title', $classGroup->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('class-groups.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Kembali ke daftar rombel</a>
        <h1 class="text-xl font-semibold text-slate-800 mt-2">{{ $classGroup->name }}</h1>
        <p class="text-sm text-slate-500">
            {{ $classGroup->gradeLevel->jenjang->name }} — {{ $classGroup->gradeLevel->name }} · {{ $classGroup->academicYear->year_name }}
            @if ($classGroup->classroom)
                · {{ $classGroup->classroom->name }}
            @endif
        </p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <p class="text-sm font-semibold text-slate-700">Siswa Aktif ({{ $activeStudents->count() }})</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama Siswa</th>
                    <th class="px-4 py-3 font-medium">Masuk Sejak</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($activeStudents as $assignment)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ $assignment->student->full_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $assignment->moved_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-4 py-6 text-center text-slate-400">
                            Belum ada siswa di rombel ini. Kelola penempatan lewat halaman
                            <a href="{{ route('class-group-students.index') }}" class="text-indigo-600 hover:underline">Plotting Siswa</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
