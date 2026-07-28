<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP — SPP TK</title>
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
                <div class="w-12 h-12 rounded-2xl bg-green-100 flex items-center justify-center mb-4">
                    <i class="ti ti-shield-check text-green-600 text-2xl"></i>
                </div>
                <h1 class="text-lg font-semibold text-slate-800">Masukkan Kode OTP</h1>
                <p class="text-sm text-slate-500 mt-1 text-center">Kode dikirim ke nomor HP terdaftar. Berlaku 5 menit.</p>
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

            <form method="POST" action="{{ route('password.otp.verify') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="phone_number" value="{{ old('phone_number', $phoneNumber) }}">

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Kode OTP</label>
                    <input type="text" name="otp_code" inputmode="numeric" maxlength="6" required autofocus
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm tracking-widest text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Password Baru</label>
                    <input type="password" name="password" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-indigo-600 text-white py-2.5 text-sm font-medium hover:bg-indigo-700 transition">
                    Reset Password
                </button>

                <a href="{{ route('password.request.otp') }}" class="block text-center text-sm text-slate-500 hover:underline">
                    Belum dapat kode? Kirim ulang
                </a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
