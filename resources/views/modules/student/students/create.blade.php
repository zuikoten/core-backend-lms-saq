@extends('layouts.staff')

@section('title', 'Tambah Siswa')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-xl font-semibold text-slate-800 mb-6">Tambah Siswa</h1>

        <form action="{{ route('students.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
                <p class="text-sm font-semibold text-slate-700">Data Siswa</p>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nama Lengkap</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('full_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nama Panggilan</label>
                        <input type="text" name="nickname" value="{{ old('nickname') }}"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">NISN <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <input type="text" name="nisn" value="{{ old('nisn') }}"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('nisn') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Jenis Kelamin</label>
                        <select name="gender" class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                            <option value="">— Pilih —</option>
                            <option value="L" @selected(old('gender') === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('gender') === 'P')>Perempuan</option>
                        </select>
                        @error('gender') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Lahir</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('birth_date') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl bg-white p-6 shadow-sm space-y-4"
                x-data="{
                    phone: @js(old('parent_phone_number', '')),
                    found: false,
                    loading: false,
                    children: [],
                    debounceTimer: null,
                    lookup() {
                        clearTimeout(this.debounceTimer);
                        const digits = this.phone.replace(/\D/g, '');
                        if (digits.length < 9) { this.reset(); return; }
                        this.debounceTimer = setTimeout(() => {
                            this.loading = true;
                            fetch(`{{ route('students.parent-lookup') }}?phone_number=${encodeURIComponent(this.phone)}`, {
                                headers: { 'Accept': 'application/json' },
                            })
                                .then((res) => res.json())
                                .then((data) => {
                                    this.loading = false;
                                    if (data.found) {
                                        this.found = true;
                                        this.children = data.children;
                                        this.$refs.fatherName.value = data.father_name ?? '';
                                        this.$refs.motherName.value = data.mother_name ?? '';
                                        this.$refs.address.value = data.address ?? '';
                                    } else {
                                        this.reset();
                                    }
                                })
                                .catch(() => { this.loading = false; });
                        }, 500);
                    },
                    reset() {
                        this.found = false;
                        this.children = [];
                        this.$refs.fatherName.value = @js(old('parent_father_name', ''));
                        this.$refs.motherName.value = @js(old('parent_mother_name', ''));
                        this.$refs.address.value = @js(old('parent_address', ''));
                    },
                }"
            >
                <div>
                    <p class="text-sm font-semibold text-slate-700">Data Orang Tua/Wali</p>
                    <p class="text-xs text-slate-400 mt-1">
                        Nomor HP dipakai orang tua untuk aktivasi akun. Kalau nomor sudah terdaftar
                        (kakak-adik), data di bawah otomatis terisi dari data yang sudah ada.
                    </p>
                </div>

                <template x-if="found">
                    <p class="text-xs text-indigo-600 bg-indigo-50 rounded-xl px-3 py-2 flex items-start gap-1.5">
                        <i class="ti ti-info-circle mt-0.5"></i>
                        <span>
                            Nomor ini sudah terdaftar sebagai orang tua dari:
                            <span class="font-medium" x-text="children.join(', ')"></span>.
                            Nama & alamat diambil otomatis dan tidak bisa diubah dari form ini.
                        </span>
                    </p>
                </template>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">
                            Nomor HP Orang Tua
                            <span x-show="loading" class="text-slate-400 font-normal">(mengecek...)</span>
                        </label>
                        <input type="text" name="parent_phone_number" x-model="phone" @input="lookup()"
                               placeholder="08xxxxxxxxxx"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('parent_phone_number') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ayah</label>
                        <input type="text" name="parent_father_name" x-ref="fatherName" value="{{ old('parent_father_name') }}"
                               :readonly="found"
                               :class="found && 'bg-slate-50 text-slate-500 cursor-not-allowed'"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('parent_father_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ibu</label>
                        <input type="text" name="parent_mother_name" x-ref="motherName" value="{{ old('parent_mother_name') }}"
                               :readonly="found"
                               :class="found && 'bg-slate-50 text-slate-500 cursor-not-allowed'"
                               class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                        @error('parent_mother_name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">Alamat <span class="text-slate-400 font-normal">(opsional)</span></label>
                        <textarea name="parent_address" x-ref="address" rows="2"
                                  :readonly="found"
                                  :class="found && 'bg-slate-50 text-slate-500 cursor-not-allowed'"
                                  class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('parent_address') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan
                </button>
                <a href="{{ route('students.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection