<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — SPP TK</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-slate-50 antialiased" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="flex min-h-screen" x-data="{ confirmingLogout: false }">
        <x-staff-sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            <x-staff-navbar :title="trim($__env->yieldContent('title', 'Dashboard'))" />

            <main class="p-6">
                @yield('content')
            </main>
        </div>

        {{-- Modal konfirmasi logout — sengaja ditaruh di level root ini (bukan
             nested di dalam sidebar), supaya overlay fixed inset-0 benar-benar
             menutupi seluruh layar (navbar + sidebar + konten) secara seragam.
             Kalau ditaruh nested di dalam <aside>, overlay ikut kepengaruh
             stacking context sidebar dan navbar jadi tidak ikut ter-dim. --}}
        <div
            x-show="confirmingLogout"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center px-4"
            style="background: rgba(15, 23, 42, 0.4);"
            @keydown.escape.window="confirmingLogout = false"
        >
            <div
                @click.outside="confirmingLogout = false"
                x-show="confirmingLogout"
                x-transition
                class="bg-white rounded-2xl p-6 w-full max-w-xs"
                style="box-shadow: 0 2px 10px rgba(20,20,50,0.06);"
            >
                <div class="w-11 h-11 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mb-4">
                    <i class="ti ti-logout text-[20px]"></i>
                </div>
                <p class="text-sm font-semibold text-slate-800 mb-1">Keluar dari akun?</p>
                <p class="text-xs text-slate-500 mb-5">Kamu perlu login ulang untuk masuk kembali.</p>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="confirmingLogout = false"
                        class="flex-1 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium py-2 hover:bg-slate-50 transition"
                    >
                        Batal
                    </button>
                    <form method="POST" action="{{ route('staff.logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full rounded-xl bg-red-500 text-white text-sm font-medium py-2 hover:bg-red-600 transition">
                            Ya, Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
