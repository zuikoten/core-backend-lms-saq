<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password — SPP TK</title>
    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="font-family: 'Plus Jakarta Sans', sans-serif;">
<div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
    <div class="w-full max-w-sm">
        <div class="bg-white rounded-2xl p-8" style="box-shadow: 0 2px 10px rgba(20,20,50,0.06);">
            <div class="flex flex-col items-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 flex items-center justify-center mb-4">
                    <i class="ti ti-lock-question text-amber-600 text-2xl"></i>
                </div>
                <h1 class="text-lg font-semibold text-slate-800">Lupa Password</h1>
                <p class="text-sm text-slate-500 mt-1 text-center">Masukkan email admin, kami kirim tautan reset.</p>
            </div>

            @if (session('status'))
                <div class="mb-4 rounded-2xl bg-green-50 border border-green-100 px-4 py-3 text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-indigo-600 text-white py-2.5 text-sm font-medium hover:bg-indigo-700 transition">
                    Kirim Tautan Reset
                </button>

                <a href="{{ route('login') }}" class="block text-center text-sm text-slate-500 hover:underline">
                    Kembali ke login
                </a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
