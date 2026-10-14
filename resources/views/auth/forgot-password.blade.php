@extends('layouts.guest')

@section('title', 'Lupa Password')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Lupa Password</h2>
    <p class="text-sm text-gray-500 mb-6">Masukkan email Anda dan kami akan mengirimkan link reset password.</p>

    {{-- Status --}}
    @if(session('status'))
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

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

        <button type="submit"
                class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg
                       hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition text-sm">
            Kirim Link Reset
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Login</a>
    </div>
@endsection
