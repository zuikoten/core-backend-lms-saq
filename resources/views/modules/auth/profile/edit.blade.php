@extends('layouts.staff')

@section('title', 'Profil Saya')

@section('content')
    <div class="max-w-xl mx-auto space-y-6">
        @if (session('status'))
            <div class="rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        {{-- Info Profil --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Info Profil</h2>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl font-semibold overflow-hidden shrink-0">
                        @if ($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name ?? ($user->email ?? $user->phone_number), 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Foto Profil</label>
                        <input type="file" name="avatar" accept="image/*"
                            class="text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-600 file:px-3 file:py-1.5 file:text-xs file:font-medium">
                    </div>
                </div>
                @error('avatar')
                    <p class="text-xs text-red-500">{{ $message }}</p>
                @enderror

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        placeholder="mis : Ahmad Budi Santoso"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        Username <span class="text-slate-400 font-normal">(opsional, buat URL profil)</span>
                    </label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}"
                        placeholder="budi-santoso56 / budi-santoso / budi_ajah"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('username')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Ganti Email --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-1">Ganti Email</h2>
            <p class="text-xs text-slate-400 mb-4">Email dipakai untuk reset password — perlu konfirmasi password lama.</p>

            <form method="POST" action="{{ route('profile.email.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email Baru</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Password Saat Ini</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Ganti Email
                    </button>
                </div>
            </form>
        </div>

        {{-- Ganti Nomor HP --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-1">Ganti Nomor HP</h2>
            <p class="text-xs text-slate-400 mb-4">Nomor HP dipakai untuk OTP login & reset password — perlu konfirmasi
                password.</p>

            <form method="POST" action="{{ route('profile.phone.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor HP Baru</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                        required
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('phone_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Password Saat Ini</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Ganti Nomor HP
                    </button>
                </div>
            </form>
        </div>

        {{-- Ganti Password --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-800 mb-4">Ganti Password</h2>

            <form method="POST" action="{{ route('profile.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Password Lama</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="current_password" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Password Baru</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }" class="relative">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Konfirmasi Password Baru</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                            <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
