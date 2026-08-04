@extends('layouts.staff')

@section('title', 'Tambah Ruang Kelas')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Tambah Ruang Kelas</h1>

        <form action="{{ route('classrooms.store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ruang</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Ruang Melati"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Kapasitas <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="number" name="capacity" value="{{ old('capacity') }}" min="1"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('capacity') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Lokasi <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: Gedung Utama, Lantai 1"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('location') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="{{ route('classrooms.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
