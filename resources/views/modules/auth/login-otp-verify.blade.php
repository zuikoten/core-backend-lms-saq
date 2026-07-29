<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP Login — SPP TK</title>
    @vite(['resources/css/app.css', 'resources/js/modules/auth/otp-input.js', 'resources/js/app.js'])
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

            <form method="POST" action="{{ route('login.otp.verify') }}" class="space-y-5" x-data="otpInput()" @paste.prevent="handlePaste($event)">
                @csrf
                <input type="hidden" name="phone_number" value="{{ old('phone_number', $phoneNumber) }}">
                <input type="hidden" name="otp_code" :value="digits.join('')">

                <div class="flex justify-center gap-2">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit0" x-model="digits[0]" @input="onInput(0, $event)" @keydown.backspace="onBackspace(0)" autofocus
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit1" x-model="digits[1]" @input="onInput(1, $event)" @keydown.backspace="onBackspace(1)"
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit2" x-model="digits[2]" @input="onInput(2, $event)" @keydown.backspace="onBackspace(2)"
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit3" x-model="digits[3]" @input="onInput(3, $event)" @keydown.backspace="onBackspace(3)"
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit4" x-model="digits[4]" @input="onInput(4, $event)" @keydown.backspace="onBackspace(4)"
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="text" inputmode="numeric" maxlength="1" x-ref="digit5" x-model="digits[5]" @input="onInput(5, $event)" @keydown.backspace="onBackspace(5)"
                        class="w-11 h-13 text-center text-lg font-semibold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-indigo-600 text-white py-2.5 text-sm font-medium hover:bg-indigo-700 transition">
                    Masuk
                </button>

                <a href="{{ route('login') }}" class="block text-center text-sm text-slate-500 hover:underline">
                    &larr; Kembali ke login
                </a>
            </form>
        </div>
    </div>
</div>
</body>
</html>