<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — Terjadi Gangguan Server | Kas Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-50 via-amber-50/40 to-rose-50/40 min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-amber-100 p-8 text-center relative overflow-hidden">
        <div class="absolute -top-12 -right-12 w-32 h-32 bg-amber-200/50 rounded-full blur-2xl pointer-events-none"></div>

        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
            ⚙️
        </div>

        <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-bold rounded-full uppercase tracking-wider mb-2">
            Error 500
        </span>

        <h1 class="text-2xl font-black text-gray-900 mt-1">Gangguan Server</h1>
        <p class="text-sm text-gray-500 mt-2 leading-relaxed">
            Terjadi kesalahan internal pada sistem saat memproses permintaan Anda. Mohon coba beberapa saat lagi.
        </p>

        <div class="mt-8 flex flex-col sm:flex-row gap-2 justify-center">
            <a href="{{ url('/dashboard') }}"
               class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                ← Kembali ke Dashboard
            </a>
            <button onclick="window.location.reload()"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition">
                Muat Ulang Halaman
            </button>
        </div>

        <div class="mt-8 pt-4 border-t border-gray-100 text-xs text-gray-400">
            💰 Sistem Kas Sekolah &copy; {{ date('Y') }}
        </div>
    </div>
</body>
</html>
