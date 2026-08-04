@extends('layouts.staff')

@section('title', 'Buat Rapor')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Buat Rapor</h1>

        <form action="{{ route('report-cards.store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Siswa</label>
                <select name="student_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((int) old('student_id') === $student->id)>{{ $student->full_name }}</option>
                    @endforeach
                </select>
                @error('student_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Semester</label>
                <select name="semester_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    @foreach ($semesters as $semester)
                        <option value="{{ $semester->id }}" @selected((int) old('semester_id') === $semester->id)>
                            {{ $semester->name }} {{ $semester->academicYear->year_name }}
                        </option>
                    @endforeach
                </select>
                @error('semester_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <p class="text-xs text-slate-400">
                Rombel diambil otomatis dari penempatan siswa yang sedang aktif di tahun ajaran semester ini.
                Siswa yang belum ditempatkan ke rombel manapun tidak bisa dibuatkan rapor.
            </p>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Buat Rapor (Draft)
                </button>
                <a href="{{ route('report-cards.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
