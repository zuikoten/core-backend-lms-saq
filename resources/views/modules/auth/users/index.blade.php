@extends('layouts.staff')

@section('title', 'Pengguna')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Pengguna</h1>
                <p class="text-sm text-slate-500">Kelola akun staf & role yang dimiliki tiap akun.</p>
            </div>
            <a href="{{ route('users.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 text-white text-sm font-medium px-4 py-2.5 hover:bg-indigo-700 transition">
                <i class="ti ti-plus text-[16px]"></i>
                Tambah User
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm px-4 py-3">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm px-4 py-3">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('users.index') }}" class="mb-4 flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari email atau nomor HP..."
                    class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
            </div>

            <select name="role" onchange="this.form.submit()"
                class="rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                <option value="">Semua Role</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit"
                class="rounded-xl bg-slate-100 text-slate-600 text-sm font-medium px-4 py-2.5 hover:bg-slate-200 transition">
                Cari
            </button>

            @if (request('search') || request('role'))
                <a href="{{ route('users.index') }}" class="text-sm text-slate-400 hover:text-slate-600 transition">
                    Reset
                </a>
            @endif
        </form>

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr
                        class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-slate-700">{{ $user->email ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $user->phone_number }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $role)
                                        <span
                                            class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">Belum ada role</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if ($user->is_active)
                                    <x-status-badge status="success">Aktif</x-status-badge>
                                @else
                                    <span
                                        class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('users.edit', $user) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                                        <i class="ti ti-pencil text-[14px]"></i> Edit
                                    </a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                                            onsubmit="return confirm('Hapus user {{ $user->email ?? $user->phone_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-100 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 transition">
                                                <i class="ti ti-trash text-[14px]"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->links() }}
        </div>
    </div>
@endsection
