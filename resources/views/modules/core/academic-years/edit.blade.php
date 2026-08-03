@extends('layouts.staff')

@section('title', 'Edit Tahun Ajaran')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Edit Tahun Ajaran</h1>

        <form action="{{ route('academic-years.update', $academicYear) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="year_name" class="block text-sm font-medium text-slate-600 mb-1">
                    Nama Tahun Ajaran
                </label>
                <input type="text" name="year_name" id="year_name" value="{{ old('year_name', $academicYear->year_name) }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('year_name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('academic-years.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection