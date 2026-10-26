@extends('layouts.app')

@section('title', 'Ganti Password')
@section('header', 'Keamanan Akun')

@section('content')
    <div class="max-w-xl mx-auto">
        {{-- Status Notifications --}}
        @if(session('success'))
            <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2 shadow-sm">
                <span>✓</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-2xl">
                    🔑
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Ubah Kata Sandi</h3>
                    <p class="text-xs text-gray-500">Pastikan akun Anda menggunakan kata sandi yang aman dan tidak mudah ditebak.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('password.update.auth') }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Password Saat Ini --}}
                <div>
                    <label for="current_password" class="block text-sm font-semibold text-gray-700 mb-1">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input id="current_password" type="password" name="current_password" required autofocus
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                                  @error('current_password') border-red-400 bg-red-50/20 @enderror"
                           placeholder="••••••••">
                    @error('current_password')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Baru --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">
                        Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input id="password" type="password" name="password" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition
                                  @error('password') border-red-400 bg-red-50/20 @enderror"
                           placeholder="Minimal 8 karakter">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Konfirmasi Password Baru --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">
                        Ulangi Password Baru <span class="text-red-500">*</span>
                    </label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm
                                  focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="Ketik ulang password baru">
                </div>

                {{-- Submit --}}
                <div class="pt-2 flex items-center justify-end gap-3">
                    <a href="{{ auth()->user()->role === 'siswa' ? route('siswa.dashboard') : route('admin.dashboard') }}"
                       class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-lg shadow-sm transition">
                        Simpan Password Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
