@extends('layouts.staff')

@section('title', 'Edit Siswa')

@section('content')
    <div class="max-w-2xl space-y-6">
        <h1 class="text-xl font-semibold text-slate-800">Edit Siswa</h1>

        @if (session('status'))
            <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        {{-- Data Siswa --}}
        <form action="{{ route('students.update', $student) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')
            <p class="text-sm font-semibold text-slate-700">Data Siswa</p>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->full_name) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('full_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Panggilan</label>
                    <input type="text" name="nickname" value="{{ old('nickname', $student->nickname) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">NISN</label>
                    <input type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('nisn') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Jenis Kelamin</label>
                    <select name="gender" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="L" @selected(old('gender', $student->gender) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('gender', $student->gender) === 'P')>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Status</label>
                    <select name="status" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        <option value="aktif" @selected(old('status', $student->status) === 'aktif')>Aktif</option>
                        <option value="mutasi" @selected(old('status', $student->status) === 'mutasi')>Mutasi</option>
                        <option value="lulus" @selected(old('status', $student->status) === 'lulus')>Lulus</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Simpan Data Siswa
            </button>
        </form>

        {{-- Data Orang Tua --}}
        <form action="{{ route('students.parent.update', $student) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')

            <div>
                <p class="text-sm font-semibold text-slate-700">Data Orang Tua/Wali</p>
                @if ($student->parentProfile->students->count() > 1)
                    <p class="text-xs text-amber-600 mt-1">
                        <i class="ti ti-alert-triangle"></i>
                        Orang tua ini juga terdaftar untuk {{ $student->parentProfile->students->count() - 1 }} siswa lain. Perubahan di bawah berlaku untuk semuanya.
                    </p>
                @endif
                <p class="text-xs text-slate-400 mt-1">
                    Nomor HP: <span class="font-medium text-slate-600">{{ $student->parentProfile->phone_number }}</span>
                    — tidak bisa diubah dari sini karena dipakai untuk login akun orang tua.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ayah</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $student->parentProfile->father_name) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('father_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ibu</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name', $student->parentProfile->mother_name) }}"
                           class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('mother_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Alamat</label>
                    <textarea name="address" rows="2"
                              class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('address', $student->parentProfile->address) }}</textarea>
                </div>
            </div>

            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Simpan Data Orang Tua
            </button>
        </form>

        <a href="{{ route('students.index') }}" class="inline-block text-sm font-medium text-slate-500 hover:text-slate-700">
            ← Kembali ke daftar siswa
        </a>
    </div>
@endsection