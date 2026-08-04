@extends('layouts.staff')

@section('title', 'Edit Semester')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Edit Semester</h1>

        <form action="{{ route('semesters.update', $semester) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')

            <div>
                <p class="block text-sm font-medium text-slate-600 mb-1">Tahun Ajaran</p>
                <p class="text-sm text-slate-500 bg-slate-50 rounded-xl px-3.5 py-2.5">{{ $semester->academicYear->year_name }}</p>
                <p class="mt-1 text-xs text-slate-400">Tidak bisa dipindah tahun ajaran lewat form ini.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Semester</label>
                <select name="name" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="Ganjil" @selected(old('name', $semester->name) === 'Ganjil')>Ganjil</option>
                    <option value="Genap" @selected(old('name', $semester->name) === 'Genap')>Genap</option>
                </select>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $semester->start_date->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('start_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $semester->end_date->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('end_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('semesters.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
