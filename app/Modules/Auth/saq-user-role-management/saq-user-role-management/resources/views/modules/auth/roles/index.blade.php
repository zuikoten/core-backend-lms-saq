@extends('layouts.staff')

@section('title', 'Role & Hak Akses')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-lg font-semibold text-slate-800">Role & Hak Akses</h1>
                <p class="text-sm text-slate-500">Kelola daftar role & permission yang dimiliki tiap role.</p>
            </div>
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 text-white text-sm font-medium px-4 py-2.5 hover:bg-indigo-700 transition">
                <i class="ti ti-plus text-[16px]"></i>
                Tambah Role
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

        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                        <th class="px-5 py-3">Nama Role</th>
                        <th class="px-5 py-3">Jumlah User</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-slate-700">{{ $role->name }}</p>
                                @if ($role->name === 'superadmin')
                                    <span class="text-xs text-slate-400">Role bawaan sistem — dikunci, tidak bisa diubah/dihapus.</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $role->users_count }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-2">
                                    @if ($role->name !== 'superadmin')
                                        <a href="{{ route('roles.edit', $role) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition">
                                            <i class="ti ti-pencil text-[14px]"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}"
                                            onsubmit="return confirm('Hapus role {{ $role->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-100 px-3 py-1.5 text-xs font-medium text-red-500 hover:bg-red-50 transition">
                                                <i class="ti ti-trash text-[14px]"></i> Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-400">
                                            <i class="ti ti-lock text-[14px]"></i> Dikunci
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">Belum ada role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
