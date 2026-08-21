<!-- resources/views/dashboard/index.blade.php -->
@extends('layouts.staff')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ================= KARTU STATISTIK RINGKAS ================= --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <x-icon-badge icon="users" color="indigo" />
                <div>
                    <p class="text-sm text-slate-500">Siswa Aktif</p>
                    <p class="text-xl font-semibold text-slate-800">{{ number_format($stats['total_siswa_aktif']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <x-icon-badge icon="school" color="blue" />
                <div>
                    <p class="text-sm text-slate-500">Rombel Aktif</p>
                    <p class="text-xl font-semibold text-slate-800">{{ number_format($stats['jumlah_rombel_aktif']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <x-icon-badge icon="calendar" color="amber" />
                <div>
                    <p class="text-sm text-slate-500">Tahun Ajaran / Semester</p>
                    <p class="text-base font-semibold text-slate-800">{{ $stats['tahun_ajaran_aktif'] }} · {{ $stats['semester_aktif'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <x-icon-badge icon="cash" color="green" />
                <div>
                    <p class="text-sm text-slate-500">Pemasukan Bulan Ini</p>
                    <p class="text-xl font-semibold text-slate-800">Rp{{ number_format($stats['pemasukan_bulan_ini'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
            <div class="flex items-center gap-3">
                <x-icon-badge icon="alert-triangle" color="red" />
                <div>
                    <p class="text-sm text-slate-500">Total Tunggakan</p>
                    <p class="text-xl font-semibold text-slate-800">Rp{{ number_format($stats['total_tunggakan'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ================= CHART KEUANGAN ================= --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-1">Tren Pemasukan SPP</h3>
            <p class="text-sm text-slate-500 mb-6">6 bulan terakhir</p>

            @php
                $maxTren = max(1, max($chart['tren_bulanan']['values']));
            @endphp

            <div class="flex items-end gap-3 h-40">
                @foreach ($chart['tren_bulanan']['labels'] as $i => $label)
                    @php $tinggi = round(($chart['tren_bulanan']['values'][$i] / $maxTren) * 100); @endphp
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full flex items-end h-32">
                            <div
                                class="w-full bg-indigo-500 rounded-t-lg transition-all"
                                style="height: {{ max($tinggi, 3) }}%"
                                title="Rp{{ number_format($chart['tren_bulanan']['values'][$i], 0, ',', '.') }}"
                            ></div>
                        </div>
                        <span class="text-xs text-slate-500">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ================= BREAKDOWN PER KANAL ================= --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-1">Pemasukan per Kanal</h3>
            <p class="text-sm text-slate-500 mb-6">Bulan berjalan</p>

            @if (empty($chart['per_kanal']['labels']))
                <p class="text-sm text-slate-400">Belum ada pembayaran bulan ini.</p>
            @else
                @php $totalKanal = max(1, array_sum($chart['per_kanal']['values'])); @endphp
                <div class="space-y-4">
                    @foreach ($chart['per_kanal']['labels'] as $i => $label)
                        @php $persen = round(($chart['per_kanal']['values'][$i] / $totalKanal) * 100); @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">{{ $label }}</span>
                                <span class="text-slate-800 font-medium">{{ $persen }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $persen }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ================= PERLU PERHATIAN ================= --}}
        <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Perlu Perhatian</h3>

            @if ($alerts['jumlah_siswa_tanpa_tarif'] > 0)
                <div class="flex items-center gap-3 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 mb-4">
                    <i class="ti ti-alert-triangle text-amber-500 text-lg"></i>
                    <p class="text-sm text-amber-800">
                        <span class="font-semibold">{{ $alerts['jumlah_siswa_tanpa_tarif'] }} siswa aktif</span>
                        belum punya pemetaan tarif SPP di tahun ajaran ini.
                    </p>
                </div>
            @endif

            @if (empty($alerts['invoice_jatuh_tempo']))
                <p class="text-sm text-slate-400">Tidak ada invoice yang jatuh tempo dalam 7 hari ke depan.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500 border-b border-slate-100">
                                <th class="py-2 font-medium">Siswa</th>
                                <th class="py-2 font-medium">No. Invoice</th>
                                <th class="py-2 font-medium">Jatuh Tempo</th>
                                <th class="py-2 font-medium text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($alerts['invoice_jatuh_tempo'] as $invoice)
                                <tr class="border-b border-slate-50">
                                    <td class="py-2 text-slate-800">{{ $invoice->nama_siswa }}</td>
                                    <td class="py-2 text-slate-500">{{ $invoice->invoice_number }}</td>
                                    <td class="py-2">
                                        <x-status-badge status="warning">{{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d M Y') }}</x-status-badge>
                                    </td>
                                    <td class="py-2 text-right text-slate-800">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- ================= AKTIVITAS TERBARU ================= --}}
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Aktivitas Terbaru</h3>

            @if (empty($activities))
                <p class="text-sm text-slate-400">Belum ada aktivitas.</p>
            @else
                <ul class="space-y-4">
                    @foreach ($activities as $item)
                        <li class="flex items-start gap-3">
                            @if ($item->tipe === 'pembayaran')
                                <x-icon-badge icon="cash" color="green" />
                            @elseif ($item->tipe === 'siswa_baru')
                                <x-icon-badge icon="user-plus" color="blue" />
                            @else
                                <x-icon-badge icon="arrows-exchange" color="amber" />
                            @endif
                            <div class="flex-1">
                                <p class="text-sm text-slate-800">{{ $item->judul }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ \Carbon\Carbon::parse($item->waktu)->diffForHumans() }}
                                    @if ($item->nominal)
                                        · Rp{{ number_format($item->nominal, 0, ',', '.') }}
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    {{-- ================= RINGKASAN AKADEMIK ================= --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Siswa per Jenjang</h3>

            @if (empty($academic['per_grade_level']))
                <p class="text-sm text-slate-400">Belum ada data plotting siswa tahun ajaran ini.</p>
            @else
                @php $maxJenjang = max(1, collect($academic['per_grade_level'])->max('jumlah_siswa')); @endphp
                <div class="space-y-4">
                    @foreach ($academic['per_grade_level'] as $row)
                        @php $persen = round(($row->jumlah_siswa / $maxJenjang) * 100); @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">{{ $row->nama_jenjang }}</span>
                                <span class="text-slate-800 font-medium">{{ $row->jumlah_siswa }} siswa</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $persen }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-4">Keterisian Rombel</h3>

            @if (empty($academic['keterisian_rombel']))
                <p class="text-sm text-slate-400">Belum ada rombel di tahun ajaran ini.</p>
            @else
                <div class="space-y-4">
                    @foreach ($academic['keterisian_rombel'] as $rombel)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-600">{{ $rombel->nama_rombel }}</span>
                                @if ($rombel->kapasitas)
                                    <span class="text-slate-800 font-medium">{{ $rombel->jumlah_siswa }} / {{ $rombel->kapasitas }}</span>
                                @else
                                    <span class="text-slate-400 text-xs">Kapasitas belum diatur</span>
                                @endif
                            </div>
                            @if ($rombel->kapasitas)
                                @php
                                    $persen = min(100, round(($rombel->jumlah_siswa / $rombel->kapasitas) * 100));
                                    $warna = $persen >= 100 ? 'bg-red-500' : ($persen >= 80 ? 'bg-amber-500' : 'bg-green-500');
                                @endphp
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="{{ $warna }} h-2 rounded-full" style="width: {{ $persen }}%"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
