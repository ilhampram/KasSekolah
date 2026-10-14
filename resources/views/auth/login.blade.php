@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Masuk ke Akun Anda</h2>

    {{-- Status --}}
    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                          @error('email') border-red-400 @enderror"
                   placeholder="email@sekolah.test">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                          @error('password') border-red-400 @enderror"
                   placeholder="••••••••">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember & Forgot --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember"
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Ingat Saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:underline">
                Lupa Password?
            </a>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg
                       hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition text-sm">
            Masuk
        </button>
    </form>

    {{-- Demo Accounts --}}
    <div class="mt-6 pt-5 border-t border-gray-100">
        <p class="text-xs text-gray-400 text-center mb-2">Akun Demo (password: <code class="bg-gray-100 px-1 rounded">password</code>)</p>
        <div class="grid grid-cols-2 gap-2 text-xs text-center">
            <div class="bg-indigo-50 rounded-lg p-2">
                <p class="font-medium text-indigo-700">Admin</p>
                <p class="text-gray-500">admin@sekolah.test</p>
            </div>
            <div class="bg-emerald-50 rounded-lg p-2">
                <p class="font-medium text-emerald-700">Siswa</p>
                <p class="text-gray-500">andi@siswa.test</p>
            </div>
        </div>
    </div>
@endsection
