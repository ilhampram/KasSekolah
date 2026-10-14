<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Login') — {{ config('app.name', 'Kas Sekolah') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-indigo-100 via-white to-emerald-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Brand --}}
        <div class="text-center mb-8">
            <span class="text-5xl">💰</span>
            <h1 class="mt-3 text-2xl font-bold text-gray-800">Sistem Kas Sekolah</h1>
            <p class="text-sm text-gray-500 mt-1">Manajemen Keuangan Kelas & Sekolah</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8">
            @yield('content')
        </div>
    </div>

</body>
</html>
