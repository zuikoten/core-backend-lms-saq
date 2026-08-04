@extends('layouts.staff')

@section('title', 'Edit Rapor')

@section('content')
    <div class="max-w-lg">
        <h1 class="text-xl font-semibold text-slate-800 mb-1">Rapor — {{ $reportCard->student->full_name }}</h1>
        <p class="text-sm text-slate-500 mb-6">
            {{ $reportCard->classGroup->name }} · {{ $reportCard->semester->name }} {{ $reportCard->semester->academicYear->year_name }}
        </p>

        @if (session('status'))
            <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('report-cards.update', $reportCard) }}" method="POST" class="rounded-2xl bg-white p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">Catatan Wali Kelas</label>
                <textarea name="summary_notes" rows="6" placeholder="Catatan perkembangan siswa selama semester ini..."
                          class="w-full rounded-xl border-slate-200 px-3.5 py-2.5 text-sm focus:border-indigo-400 focus:ring-indigo-400">{{ old('summary_notes', $reportCard->summary_notes) }}</textarea>
                @error('summary_notes') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <p class="text-xs text-slate-400">
                Kerangka rapor ini belum termasuk nilai per mata pelajaran — menyusul begitu modul Exam/Learning digarap.
            </p>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Simpan Catatan
                </button>
                <a href="{{ route('report-cards.index') }}" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100">
                    Kembali
                </a>
            </div>
        </form>
    </div>
@endsection
