@props(['title' => 'Dashboard'])

<header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-6 sticky top-0 z-10">
    <div>
        <h1 class="text-base font-semibold text-slate-800">{{ $title }}</h1>
    </div>

    <div class="flex items-center gap-4">
        <div class="relative hidden md:block">
            <i class="ti ti-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]"></i>
            <input type="text" placeholder="Cari..."
                   class="w-64 rounded-full border border-slate-200 bg-slate-50 pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white">
        </div>

        <button class="w-9 h-9 rounded-full hover:bg-slate-50 flex items-center justify-center text-slate-500 relative">
            <i class="ti ti-bell text-[18px]"></i>
            <span class="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-red-500"></span>
        </button>

        <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-semibold">
            {{ strtoupper(substr(auth()->user()->email, 0, 1)) }}
        </div>
    </div>
</header>
