@extends('layouts.staff')

@section('title', 'Edit Role')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-lg font-semibold text-slate-800 mb-5">Edit Role</h1>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <form method="POST" action="{{ route('roles.update', $role) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Role</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required
                        class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Permission</label>

                    @php
                        $rolePermissionNames = old('permissions', $role->permissions->pluck('name')->toArray());
                    @endphp

                    <div class="space-y-4">
                        @foreach ($permissionGroups as $domain => $permissions)
                            <div class="rounded-xl border border-slate-200 p-3.5">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">
                                    {{ ucfirst($domain) }}
                                </p>
                                <div class="space-y-1.5">
                                    @foreach ($permissions as $permission)
                                        <label class="flex items-center gap-2 text-sm text-slate-600">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                                {{ in_array($permission->name, $rolePermissionNames) ? 'checked' : '' }}
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                                            {{ $permission->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit"
                        class="rounded-xl bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 hover:bg-indigo-700 transition">
                        Simpan
                    </button>
                    <a href="{{ route('roles.index') }}"
                        class="rounded-xl bg-slate-100 text-slate-600 text-sm font-medium px-5 py-2.5 hover:bg-slate-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
