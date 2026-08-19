@extends('layouts.staff')

@section('title', 'Tambah User')

@section('content')
    <div class="max-w-xl mx-auto">
        <h1 class="text-lg font-semibold text-slate-800 mb-5">Tambah User</h1>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nomor HP</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                        placeholder="08xxxxxxxxxx"
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('phone_number')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Role</label>
                    <div class="space-y-2 rounded-xl border border-slate-200 p-3.5 max-h-56 overflow-y-auto">
                        @forelse ($roles as $role)
                            <label class="flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                    {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                {{ $role->name }}
                            </label>
                        @empty
                            <p class="text-xs text-slate-400">Belum ada role — buat dulu di menu Role & Hak Akses.</p>
                        @endforelse
                    </div>
                    @error('roles')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Simpan
                    </button>
                    <a href="{{ route('users.index') }}"
                        class="rounded-xl bg-slate-100 text-slate-600 text-sm font-medium px-5 py-2.5 hover:bg-slate-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
