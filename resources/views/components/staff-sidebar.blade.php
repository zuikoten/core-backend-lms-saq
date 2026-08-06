@php
    // Modul yang sudah punya halaman nyata. Selain ini, link diarahkan ke "#"
    // supaya tidak ada dead link ke modul yang belum dibangun skemanya.
    $builtRoutes = [
        'dashboard' => route('staff.dashboard'),
        'academic-years' => route('academic-years.index'),
    ];
@endphp

<aside x-data="{
    mobileOpen: false,
    openGroup: '{{ request()->routeIs(['class-groups.*', 'class-group-students.*', 'report-cards.*'])
        ? 'akademik'
        : (request()->routeIs(['finance.*'])
            ? 'keuangan'
            : (request()->routeIs(['academic-years.*', 'jenjang.*', 'grade-levels.*', 'semesters.*', 'classrooms.*'])
                ? 'data-master'
                : null)) }}'
}"
    class="w-64 shrink-0 bg-white border-r border-slate-100 flex flex-col h-screen sticky top-0">
    {{-- Brand --}}
    <div class="flex items-center gap-2.5 px-5 h-16 shrink-0">
        <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center">
            <i class="ti ti-school text-white text-lg"></i>
        </div>
        <div>
            <span class="font-semibold text-slate-800 text-[15px] block">SAQ</span>
            <span class="font-medium text-slate-400 text-[11px] block leading-tight">Learning Management System</span>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 pb-4 space-y-5">
        {{-- MENU UTAMA --}}
        <div>
            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Menu</p>

            <a href="{{ $builtRoutes['dashboard'] }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('staff.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100' }}">
                <i class="ti ti-layout-dashboard text-[18px]"></i>
                Dashboard
            </a>

            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                <i class="ti ti-clipboard-list text-[18px]"></i>
                PPDB
            </a>
        </div>

        {{-- AKADEMIK --}}
        <div>
            <button @click="openGroup = openGroup === 'akademik' ? null : 'akademik'"
                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                <span class="flex items-center gap-3">
                    <i class="ti ti-books text-[18px]"></i>
                    Akademik
                </span>
                <i class="ti ti-chevron-down text-[16px] transition-transform"
                    :class="openGroup === 'akademik' && 'rotate-180'"></i>
            </button>
            <div x-show="openGroup === 'akademik'" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
                <a href="{{ route('class-groups.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('class-groups.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Kelas & Rombel
                </a>
                <a href="{{ route('class-group-students.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('class-group-students.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Plotting Siswa
                </a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Jadwal
                    Pelajaran</a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Materi</a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Presensi</a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Penilaian
                    / Ujian</a>
                <a href="{{ route('report-cards.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                  {{ request()->routeIs('report-cards.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Rapor
                </a>
            </div>
        </div>

        <a href="{{ route('students.index') }}"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
             {{ request()->routeIs('students.*') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-100' }}">
            <i class="ti ti-users text-[18px]"></i>
            Siswa & Guru
        </a>

        {{-- KEUANGAN --}}
        <div>
            <button @click="openGroup = openGroup === 'keuangan' ? null : 'keuangan'"
                class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                <span class="flex items-center gap-3">
                    <i class="ti ti-cash text-[18px]"></i>
                    Keuangan
                </span>
                <i class="ti ti-chevron-down text-[16px] transition-transform"
                    :class="openGroup === 'keuangan' && 'rotate-180'"></i>
            </button>
            <div x-show="openGroup === 'keuangan'" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
                <a href="{{ route('finance.billing-types.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                     {{ request()->routeIs('finance.billing-types.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Jenis Tagihan
                </a>
                <a href="{{ route('finance.payment-channels.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                     {{ request()->routeIs('finance.payment-channels.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Kanal Pembayaran
                </a>
                <a href="{{ route('finance.billing-tariffs.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                    {{ request()->routeIs('finance.billing-tariffs.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Tarif & Tagihan
                </a>
                <a href="{{ route('finance.student-tariff-mappings.index') }}"
                    class="block px-3 py-2 rounded-lg text-sm transition
                    {{ request()->routeIs('finance.student-tariff-mappings.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                    Pemetaan Tarif Siswa
                </a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Pembayaran</a>
                <a href="#"
                    class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Laporan
                    Keuangan</a>
            </div>
        </div>

        <a href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
            <i class="ti ti-book-2 text-[18px]"></i>
            Perpustakaan
        </a>

        <a href="#"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
            <i class="ti ti-tools-kitchen-2 text-[18px]"></i>
            Kantin
        </a>

        {{-- LAINNYA --}}
        <div>
            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Lainnya</p>

            <div>
                <button @click="openGroup = openGroup === 'data-master' ? null : 'data-master'"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                    <span class="flex items-center gap-3">
                        <i class="ti ti-database text-[18px]"></i>
                        Data Master
                    </span>
                    <i class="ti ti-chevron-down text-[16px] transition-transform"
                        :class="openGroup === 'data-master' && 'rotate-180'"></i>
                </button>
                <div x-show="openGroup === 'data-master'" x-collapse class="pl-11 pr-3 space-y-1 mt-1">
                    <a href="{{ $builtRoutes['academic-years'] }}"
                        class="block px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('academic-years.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        Tahun Ajaran
                    </a>

                    <a href="{{ route('jenjang.index') }}"
                        class="block px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('jenjang.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">Jenjang</a>

                    <a href="{{ route('grade-levels.index') }}"
                        class="block px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('grade-levels.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">Tingkat
                        /Grade Level</a>

                    <a href="{{ route('semesters.index') }}"
                        class="block px-3 py-2 rounded-lg text-sm transition
                      {{ request()->routeIs('semesters.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">Semester</a>

                    <a href="{{ route('classrooms.index') }}"
                        class="block px-3 py-2 rounded-lg text-sm transition
                        {{ request()->routeIs('classrooms.*') ? 'text-indigo-600 font-medium' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
                        Ruang Kelas
                    </a>
                    <a href="#"
                        class="block px-3 py-2 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700">Master
                        Mapel</a>
                </div>
            </div>

            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                <i class="ti ti-bell text-[18px]"></i>
                Notifikasi
            </a>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition">
                <i class="ti ti-settings text-[18px]"></i>
                Pengaturan
            </a>
        </div>
    </nav>

    {{-- User card --}}
    <div class="p-3 border-t border-slate-100 relative">
        <button type="button" @click="confirmingLogout = true"
            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-red-100 transition text-left group">
            <div
                class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 group-hover:bg-red-200 group-hover:text-red-600 flex items-center justify-center text-sm font-semibold transition-colors">
                {{ strtoupper(substr(auth()->user()->email, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-700 group-hover:text-red-800 truncate transition-colors">
                    {{ auth()->user()->email }}</p>
                <p class="text-xs text-slate-400 group-hover:text-red-500 transition-colors">
                    {{ ucfirst(auth()->user()->getRoleNames()->first() ?? '-') }}</p>
            </div>
            <i class="ti ti-logout text-slate-400 group-hover:text-red-500 text-[16px] transition-colors"></i>
        </button>
    </div>
</aside>
