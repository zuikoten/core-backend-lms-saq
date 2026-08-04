@extends('layouts.staff')

@section('title', 'Edit Rombel')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Edit Rombel</h1>

        <form action="{{ route('class-groups.update', $classGroup) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')

            <p class="text-xs text-slate-400 bg-slate-50 rounded-xl px-3.5 py-2.5">
                Tingkat & Tahun Ajaran tidak bisa diubah dari sini — buat rombel baru kalau butuh pindah.
            </p>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Nama Rombel</label>
                <input type="text" name="name" value="{{ old('name', $classGroup->name) }}"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Ruang Kelas</label>
                <select name="classroom_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Belum ditentukan —</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected((int) old('classroom_id', $classGroup->classroom_id) === $classroom->id)>{{ $classroom->name }}</option>
                    @endforeach
                </select>
                @error('classroom_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan Perubahan
                </button>
                <a href="{{ route('class-groups.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
