@extends('layouts.staff')

@section('title', 'Konfirmasi Pemetaan Massal')

@section('content')
<div class="max-w-2xl">
    <h1 class="mb-2 text-xl font-semibold text-slate-800">Konfirmasi Pemetaan Massal</h1>
    <p class="mb-6 text-sm text-slate-500">
        Tarif: <strong>{{ $billingTariff->billingType->name }} — {{ $billingTariff->tariff_name }}</strong>
        ({{ $billingTariff->academicYear->year_name }}) — Rp{{ number_format($billingTariff->amount, 0, ',', '.') }}
    </p>

    @if ($eligibleStudents->isEmpty())
        <div class="rounded-2xl bg-white p-6 text-center text-slate-400 shadow-sm">
            Tidak ada siswa yang cocok dengan cakupan ini — kemungkinan semua siswa di cakupan ini sudah punya pemetaan tarif untuk jenis tagihan yang sama.
        </div>
        <a href="{{ route('finance.student-tariff-mappings.bulk-create') }}" class="mt-4 inline-block rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Kembali</a>
    @else
        <form action="{{ route('finance.student-tariff-mappings.bulk-store') }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm">
            @csrf
            <input type="hidden" name="billing_tariff_id" value="{{ $billingTariff->id }}">
            <input type="hidden" name="note" value="{{ $note }}">
            <input type="hidden" name="approved_by" value="{{ $approvedBy }}">

            <div class="mb-3 flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                    <input type="checkbox" id="select-all" checked
                           onclick="document.querySelectorAll('.student-checkbox').forEach(el => el.checked = this.checked)">
                    Pilih Semua ({{ $eligibleStudents->count() }} siswa)
                </label>
            </div>

            <div class="max-h-96 space-y-1 overflow-y-auto border-y border-slate-100 py-2">
                @foreach ($eligibleStudents as $student)
                    <label class="flex items-center gap-2 rounded-lg px-2 py-2 text-sm text-slate-600 hover:bg-slate-50">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox" checked>
                        {{ $student->full_name }}
                    </label>
                @endforeach
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-indigo-700">Simpan Pemetaan</button>
                <a href="{{ route('finance.student-tariff-mappings.bulk-create') }}" class="rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-200">Kembali</a>
            </div>
        </form>
    @endif
</div>
@endsection