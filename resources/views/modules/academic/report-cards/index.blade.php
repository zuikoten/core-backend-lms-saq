@extends('layouts.staff')

@section('title', 'Rapor')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Rapor</h1>
            <p class="text-sm text-slate-500">
                @if ($activeSemester)
                    Menampilkan semester aktif: <span class="font-medium text-slate-600">{{ $activeSemester->name }} {{ $activeSemester->academicYear->year_name }}</span>
                @else
                    Belum ada semester aktif.
                @endif
            </p>
        </div>
        <a href="{{ route('report-cards.create') }}"
           class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700">
            <i class="ti ti-plus"></i>
            Buat Rapor
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-2xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700 shadow-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3 font-medium">Siswa</th>
                    <th class="px-4 py-3 font-medium">Rombel</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($reportCards as $reportCard)
                    <tr>
                        <td class="px-4 py-3 text-slate-700">{{ $reportCard->student->full_name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $reportCard->classGroup->name }}</td>
                        <td class="px-4 py-3">
                            @if ($reportCard->status === 'published')
                                <x-status-badge status="success">Published</x-status-badge>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-500">Draft</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($reportCard->status === 'draft')
                                    <a href="{{ route('report-cards.edit', $reportCard) }}"
                                       class="rounded-xl px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                        Edit
                                    </a>
                                    <form action="{{ route('report-cards.publish', $reportCard) }}" method="POST"
                                          onsubmit="return confirm('Publikasikan rapor {{ $reportCard->student->full_name }}? Setelah ini orang tua bisa langsung melihatnya.');">
                                        @csrf
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                            Publish
                                        </button>
                                    </form>
                                    <form action="{{ route('report-cards.destroy', $reportCard) }}" method="POST"
                                          onsubmit="return confirm('Hapus rapor ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-xl px-3 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                            Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-400">Dipublikasikan {{ $reportCard->published_at->format('d M Y') }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-slate-400">Belum ada rapor untuk semester ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
