@extends('layouts.staff')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Laporan Keuangan</h1>
        <p class="text-sm text-slate-500">Rekap & analisis data keuangan sekolah.</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <a href="{{ route('finance.reports.monthly-recap') }}" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <i class="ti ti-calendar-stats text-lg"></i>
            </div>
            <p class="font-medium text-slate-800">Rekap SPP per Bulan</p>
            <p class="text-sm text-slate-500">Total tagihan vs terbayar vs tunggakan per periode.</p>
        </a>

        <a href="{{ route('finance.reports.outstanding') }}" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <i class="ti ti-alert-triangle text-lg"></i>
            </div>
            <p class="font-medium text-slate-800">Daftar Tunggakan</p>
            <p class="text-sm text-slate-500">Siswa yang belum lunas, bisa difilter per rombel.</p>
        </a>

        <a href="{{ route('finance.reports.payment-channel-recap') }}" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <i class="ti ti-credit-card text-lg"></i>
            </div>
            <p class="font-medium text-slate-800">Rekap per Kanal Pembayaran</p>
            <p class="text-sm text-slate-500">Breakdown uang masuk per kanal (tunai/transfer/dst).</p>
        </a>

        <a href="{{ route('finance.reports.component-breakdown') }}" class="rounded-2xl bg-white p-6 shadow-sm transition hover:shadow-md">
            <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <i class="ti ti-chart-pie text-lg"></i>
            </div>
            <p class="font-medium text-slate-800">Breakdown Komponen Biaya</p>
            <p class="text-sm text-slate-500">Uang masuk dipisah per komponen (SPP, Kegiatan, dll), alokasi FIFO.</p>
        </a>
    </div>
</div>
@endsection