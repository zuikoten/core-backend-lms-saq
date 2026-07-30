<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — SPP TK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="min-h-screen flex items-center justify-center bg-slate-50 px-4">
        <div class="w-full max-w-sm">
            <div class="bg-white rounded-2xl p-8" style="box-shadow: 0 2px 10px rgba(20,20,50,0.06);"
                x-data="{ method: 'password' }">
                <div class="flex flex-col items-center mb-6">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center mb-4">
                        <i class="ti ti-school text-indigo-600 text-2xl"></i>
                    </div>
                    <h1 class="text-lg font-semibold text-slate-800">Masuk ke Panel Sekolah</h1>
                    <p class="text-sm text-slate-500 mt-1">SAQ Learning Management System</p>
                </div>

                {{-- Tab switcher --}}
                <div class="flex rounded-xl bg-slate-100 p-1 mb-5 text-sm font-medium">
                    <button type="button" @click="method = 'password'"
                        :class="method === 'password' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                        class="flex-1 rounded-lg py-1.5 transition">
                        Email & Password
                    </button>
                    <button type="button" @click="method = 'otp'"
                        :class="method === 'otp' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500'"
                        class="flex-1 rounded-lg py-1.5 transition">
                        OTP WhatsApp
                    </button>
                </div>

                @if ($errors->any())
                    <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form: Email & Password --}}
                <form x-show="method === 'password'" x-cloak method="POST" action="{{ route('login.attempt') }}"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Password Input dengan toggle show/hide password -->
                    <div x-data="{ show: false }" class="relative">
                        <label class="block text-sm font-medium text-slate-600 mb-1">Password</label>
                        <div class="relative">
                            <input :type="show ? 'text' : 'password'" name="password" required
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 pr-11 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600">
                                <i class="ti" :class="show ? 'ti-eye-off' : 'ti-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-slate-500">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                            Ingat saya
                        </label>
                        <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">Lupa
                            password?</a>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-indigo-600 text-white py-2.5 text-sm font-medium hover:bg-indigo-700 transition">
                        Masuk
                    </button>
                </form>

                {{-- Form: OTP WhatsApp --}}
                <form x-show="method === 'otp'" x-cloak method="POST" action="{{ route('login.otp.request') }}"
                    class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nomor HP</label>
                        <input type="tel" name="phone_number" placeholder="08xxxxxxxxxx"
                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-indigo-600 text-white py-2.5 text-sm font-medium hover:bg-indigo-700 transition">
                        Kirim Kode OTP
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
