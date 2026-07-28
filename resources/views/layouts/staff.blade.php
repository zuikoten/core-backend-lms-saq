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
    <div class="flex min-h-screen">
        <x-staff-sidebar />

        <div class="flex-1 flex flex-col min-w-0">
            <x-staff-navbar :title="trim($__env->yieldContent('title', 'Dashboard'))" />

            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
