@extends('layouts.staff')

@section('title', 'Plotting Siswa')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-slate-800">Plotting Siswa ke Rombel</h1>
        <p class="text-sm text-slate-500">
            @if ($academicYear)
                Tahun ajaran aktif: <span class="font-medium text-slate-600">{{ $academicYear->year_name }}</span>
            @else
                Belum ada tahun ajaran aktif.
            @endif
        </p>
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

    @if (! $academicYear)
        <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 shadow-sm">
            Aktifkan tahun ajaran dulu lewat menu Tahun Ajaran sebelum bisa menempatkan siswa ke rombel.
        </div>
    @elseif ($classGroups->isEmpty())
        <div class="rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-700 shadow-sm">
            Belum ada rombel untuk tahun ajaran ini. Buat rombel dulu lewat menu
            <a href="{{ route('class-groups.create') }}" class="underline">Rombel</a>.
        </div>
    @else
        @php
            $payload = [
                'students' => $students->map(function ($student) use ($currentAssignments) {
                    $assignment = $currentAssignments->get($student->id);

                    return [
                        'id' => $student->id,
                        'name' => $student->full_name,
                        'initials' => collect(preg_split('/\s+/', trim($student->full_name)))
                            ->take(2)
                            ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
                            ->implode(''),
                        'class_group_id' => $assignment?->class_group_id,
                        'class_group_name' => $assignment?->classGroup->name,
                    ];
                })->values(),
                'classGroups' => $classGroups
                    ->map(fn ($group) => ['id' => $group->id, 'name' => $group->name])
                    ->values(),
            ];
        @endphp

        <div x-data="classGroupPlotting(@js($payload))">
            {{-- Progres penempatan --}}
            <div class="mb-4 rounded-2xl bg-white px-5 py-4 shadow-sm">
                <div class="mb-2 flex items-center justify-between text-xs">
                    <span class="font-medium text-slate-600">Progres penempatan</span>
                    <span class="text-slate-500" x-text="placedCount + ' dari ' + students.length + ' siswa sudah ditempatkan'"></span>
                </div>
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                         :style="'width: ' + progress + '%'"></div>
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm">
                {{-- Tab pill --}}
                <div class="flex gap-1.5 overflow-x-auto border-b border-slate-100 p-3">
                    <template x-for="tab in tabs" :key="tab.key">
                        <button type="button" @click="setTab(tab.key)"
                                class="flex shrink-0 items-center gap-2 rounded-xl px-3.5 py-2 text-sm font-medium transition"
                                :class="activeTab === tab.key ? 'bg-indigo-50 text-indigo-600' : 'text-slate-500 hover:bg-slate-50'">
                            <span x-text="tab.label"></span>
                            <span class="rounded-full px-2 py-0.5 text-xs"
                                  :class="activeTab === tab.key
                                      ? 'bg-white text-indigo-600'
                                      : (tab.key === 'unassigned' && tab.count > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500')"
                                  x-text="tab.count"></span>
                        </button>
                    </template>
                </div>

                {{-- Toolbar: pilih semua + cari --}}
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-4 py-3">
                    <label class="flex items-center gap-3 text-sm text-slate-600">
                        <input type="checkbox"
                               class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                               :checked="allVisibleSelected"
                               :disabled="filteredStudents.length === 0"
                               @change="toggleAllVisible($event.target.checked)">
                        <span x-text="'Pilih semua (' + filteredStudents.length + ')'"></span>
                    </label>

                    <div class="relative w-full max-w-xs">
                        <i class="ti ti-search absolute left-3 top-1/2 -translate-y-1/2 text-[15px] text-slate-400"></i>
                        <input type="text" x-model="search" placeholder="Cari nama siswa..."
                               class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-9 pr-3 text-sm focus:border-indigo-400 focus:bg-white focus:ring-indigo-400">
                    </div>
                </div>

                {{-- Daftar siswa --}}
                <ul class="divide-y divide-slate-100">
                    <template x-for="student in filteredStudents" :key="student.id">
                        <li>
                            <label class="flex cursor-pointer items-center gap-3 px-4 py-3 transition hover:bg-slate-50"
                                   :class="isSelected(student.id) && 'bg-indigo-50/50'">
                                <input type="checkbox"
                                       class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                                       :checked="isSelected(student.id)"
                                       @change="toggle(student.id)">

                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold"
                                      :class="avatarClass(student.id)" x-text="student.initials"></span>

                                <span class="min-w-0 flex-1 truncate text-sm font-medium text-slate-700" x-text="student.name"></span>

                                <template x-if="activeTab === 'all'">
                                    <span>
                                        <span x-show="student.class_group_id"
                                              class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-600"
                                              x-text="student.class_group_name"></span>
                                        <span x-show="!student.class_group_id"
                                              class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-600">
                                            Belum ditempatkan
                                        </span>
                                    </span>
                                </template>
                            </label>
                        </li>
                    </template>
                </ul>

                {{-- Empty state --}}
                <div x-show="filteredStudents.length === 0" x-cloak class="px-4 py-12 text-center">
                    <template x-if="search.trim() !== ''">
                        <p class="text-sm text-slate-400">Tidak ada siswa yang cocok dengan pencarian.</p>
                    </template>
                    <template x-if="search.trim() === '' && activeTab === 'unassigned'">
                        <p class="text-sm text-slate-500">Semua siswa sudah ditempatkan 🎉</p>
                    </template>
                    <template x-if="search.trim() === '' && typeof activeTab === 'number'">
                        <p class="text-sm text-slate-400">Belum ada siswa di rombel ini. Pilih siswa dari tab "Belum Ditempatkan".</p>
                    </template>
                    <template x-if="search.trim() === '' && activeTab === 'all'">
                        <p class="text-sm text-slate-400">Belum ada siswa aktif.</p>
                    </template>
                </div>
            </div>

            {{-- Bulk action bar: muncul hanya saat ada siswa dipilih --}}
            <form action="{{ route('class-group-students.bulk') }}" method="POST"
                  x-show="selectedIds.length > 0" x-cloak
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="translate-y-2 opacity-0"
                  x-transition:enter-end="translate-y-0 opacity-100"
                  class="sticky bottom-4 z-20 mt-4 flex flex-wrap items-center gap-3 rounded-2xl bg-slate-800 px-5 py-3 text-white shadow-lg">
                @csrf

                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="student_ids[]" :value="id">
                </template>

                <p class="text-sm">
                    <span class="font-semibold" x-text="selectedIds.length"></span> siswa dipilih
                </p>
                <button type="button" @click="selectedIds = []" class="text-xs text-slate-300 underline hover:text-white">
                    Batal pilih
                </button>

                <div class="ml-auto flex items-center gap-2">
                    <select name="class_group_id" x-model="targetClassGroupId" required
                            class="rounded-xl border-0 bg-white px-3 py-2 text-xs text-slate-700 focus:ring-2 focus:ring-indigo-400">
                        <option value="">— Pilih rombel tujuan —</option>
                        <template x-for="group in targetOptions" :key="group.id">
                            <option :value="group.id" x-text="group.name"></option>
                        </template>
                    </select>

                    <button type="submit" :disabled="!targetClassGroupId"
                            class="rounded-xl bg-indigo-500 px-4 py-2 text-xs font-medium text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-50">
                        <span x-text="actionLabel"></span>
                        <span x-text="'(' + selectedIds.length + ')'"></span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    @push('scripts')
        @vite(['resources/js/modules/academic/class-group-students-index.js'])
    @endpush
@endsection
