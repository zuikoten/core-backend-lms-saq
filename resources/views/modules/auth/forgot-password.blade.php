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
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl p-8" style="box-shadow: 0 2px 10px rgba(20,20,50,0.06);">
            <div class="flex flex-col items-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4">
                    <i class="ti ti-lock-question text-indigo-600 text-2xl"></i>
                </div>
                <h1 class="text-lg font-semibold text-slate-800">Lupa Password</h1>
                <p class="text-sm text-slate-500 mt-1 text-center">Pilih cara reset password kamu.</p>
            </div>

            <div class="space-y-3">
                <a href="{{ route('password.request.email') }}"
                   class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40 transition">
                    <div class="w-9 h-9 rounded-[10px] bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="ti ti-mail text-[18px]"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-800">Lewat Email</p>
                        <p class="text-xs text-slate-500">Tautan reset dikirim ke email akun kamu</p>
                    </div>
                    <i class="ti ti-chevron-right text-slate-400"></i>
                </a>

                <a href="{{ route('password.request.otp') }}"
                   class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4 hover:border-indigo-300 hover:bg-indigo-50/40 transition">
                    <div class="w-9 h-9 rounded-[10px] bg-green-50 text-green-600 flex items-center justify-center">
                        <i class="ti ti-brand-whatsapp text-[18px]"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-slate-800">Lewat OTP WhatsApp</p>
                        <p class="text-xs text-slate-500">Kode OTP dikirim ke nomor HP terdaftar</p>
                    </div>
                    <i class="ti ti-chevron-right text-slate-400"></i>
                </a>
            </div>

            <a href="{{ route('login') }}" class="block text-center text-sm text-slate-500 hover:underline mt-6">
                Kembali ke login
            </a>
        </div>
    </div>
</div>
</body>
</html>
