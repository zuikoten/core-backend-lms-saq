@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-2xl p-6" style="box-shadow: 0 2px 10px rgba(20,20,50,0.06);">
        <div class="flex items-center gap-3">
            <x-icon-badge icon="users" color="indigo" />
            <div>
                <p class="text-sm text-slate-500">Selamat datang</p>
                <p class="text-base font-semibold text-slate-800">{{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
