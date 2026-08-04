@extends('layouts.staff')

@section('title', 'Tambah Semester')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Tambah Semester</h1>

        <form action="{{ route('semesters.store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Tahun Ajaran</label>
                <select name="academic_year_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected((int) old('academic_year_id') === $academicYear->id)>{{ $academicYear->year_name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Semester</label>
                <select name="name" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    <option value="Ganjil" @selected(old('name') === 'Ganjil')>Ganjil</option>
                    <option value="Genap" @selected(old('name') === 'Genap')>Genap</option>
                </select>
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('start_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('end_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <p class="text-xs text-slate-400">
                Semester baru akan dibuat dalam status tidak aktif. Aktifkan lewat halaman daftar setelah dibuat.
            </p>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="{{ route('semesters.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
