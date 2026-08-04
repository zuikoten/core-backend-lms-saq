@extends('layouts.staff')

@section('title', 'Plotting Siswa')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-800">Plotting Siswa ke Rombel</h1>
        <p class="text-sm text-slate-500">
            @if ($academicYear)
                Tahun ajaran aktif: <span class="font-medium text-slate-600">{{ $academicYear->year_name }}</span>
            @else
                Belum ada tahun ajaran aktif.
            @endif
        </p>
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

    @if (! $academicYear)
        <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 shadow-sm">
            Aktifkan tahun ajaran dulu lewat menu Tahun Ajaran sebelum bisa menempatkan siswa ke rombel.
        </div>
    @elseif ($classGroups->isEmpty())
        <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 shadow-sm">
            Belum ada rombel untuk tahun ajaran ini. Buat rombel dulu lewat menu
            <a href="{{ route('class-groups.create') }}" class="underline">Rombel</a>.
        </div>
    @else
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama Siswa</th>
                        <th class="px-4 py-3 font-medium">Rombel Saat Ini</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        @php $assignment = $currentAssignments->get($student->id); @endphp
                        <tr>
                            <td class="px-4 py-3 text-slate-700">{{ $student->full_name }}</td>
                            <td class="px-4 py-3 text-slate-500">
                                {{ $assignment?->classGroup->name ?? '— Belum ditempatkan —' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($assignment)
                                    <form action="{{ route('class-group-students.transfer', $assignment) }}" method="POST"
                                          class="flex items-center justify-end gap-2">
                                        @csrf
                                        <select name="target_class_group_id" required
                                                class="rounded-xl border-slate-200 px-2.5 py-1.5 text-xs focus:border-indigo-400 focus:ring-indigo-400">
                                            <option value="">— Pindah ke —</option>
                                            @foreach ($classGroups as $classGroup)
                                                @unless ($classGroup->id === $assignment->class_group_id)
                                                    <option value="{{ $classGroup->id }}">{{ $classGroup->name }}</option>
                                                @endunless
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                            Pindahkan
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('class-group-students.store') }}" method="POST"
                                          class="flex items-center justify-end gap-2">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <select name="class_group_id" required
                                                class="rounded-xl border-slate-200 px-2.5 py-1.5 text-xs focus:border-indigo-400 focus:ring-indigo-400">
                                            <option value="">— Tempatkan ke —</option>
                                            @foreach ($classGroups as $classGroup)
                                                <option value="{{ $classGroup->id }}">{{ $classGroup->name }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-emerald-600 hover:bg-emerald-50">
                                            Tempatkan
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-slate-400">Belum ada siswa aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
@endsection
