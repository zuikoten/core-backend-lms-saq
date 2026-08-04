@extends('layouts.staff')

@section('title', 'Tambah Tingkat')

@section('content')
    <div class="max-w-md">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Tambah Tingkat / Grade Level</h1>

        <form action="{{ route('grade-levels.store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Jenjang</label>
                <select name="jenjang_id" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    <option value="">— Pilih —</option>
                    @foreach ($jenjangList as $jenjang)
                        <option value="{{ $jenjang->id }}" @selected((int) old('jenjang_id') === $jenjang->id)>{{ $jenjang->name }}</option>
                    @endforeach
                </select>
                @error('jenjang_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Nama Tingkat</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: TK-A"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Urutan Tampil <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                       class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                @error('sort_order') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="{{ route('grade-levels.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
