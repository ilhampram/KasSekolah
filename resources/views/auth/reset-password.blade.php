@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Reset Password</h2>

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                          @error('email') border-red-400 @enderror">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
            <input id="password" type="password" name="password" required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                          @error('password') border-red-400 @enderror"
                   placeholder="Minimal 8 karakter">
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                   placeholder="Ulangi password baru">
        </div>

        <button type="submit"
                class="w-full py-2.5 bg-indigo-600 text-white font-semibold rounded-lg
                       hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-200 transition text-sm">
            Reset Password
        </button>
    </form>
@endsection
