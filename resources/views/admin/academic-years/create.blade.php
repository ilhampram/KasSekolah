@extends('layouts.app')

@section('title', 'Tambah Tahun Ajaran')
@section('header', 'Tambah Tahun Ajaran')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.academic-years.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Daftar Tahun Ajaran</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Form Tambah Tahun Ajaran</h3>

            <form method="POST" action="{{ route('admin.academic-years.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                    <input type="text" id="year" name="year" value="{{ old('year', date('Y').'/'.(date('Y')+1)) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('year') border-red-400 @enderror"
                           placeholder="Contoh: 2026/2027">
                    <p class="mt-1 text-xs text-gray-400">Format wajib: YYYY/YYYY</p>
                    @error('year')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                    <select id="semester" name="semester" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('semester') border-red-400 @enderror">
                        <option value="Ganjil" {{ old('semester') === 'Ganjil' ? 'selected' : '' }}>Ganjil</option>
                        <option value="Genap" {{ old('semester') === 'Genap' ? 'selected' : '' }}>Genap</option>
                    </select>
                    @error('semester')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">Set sebagai Tahun Ajaran Aktif</span>
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Simpan
                    </button>
                    <a href="{{ route('admin.academic-years.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
