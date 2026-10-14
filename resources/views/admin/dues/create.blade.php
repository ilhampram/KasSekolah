@extends('layouts.app')

@section('title', 'Generate Tagihan Massal')
@section('header', 'Generate Tagihan Massal')

@section('content')
    <div class="max-w-2xl" x-data="{ targetType: '{{ old('target_type', 'single') }}' }">
        <div class="mb-6">
            <a href="{{ route('admin.dues.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Daftar Tagihan</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-2">Generate Tagihan Massal Siswa</h3>
            <p class="text-sm text-gray-500 mb-6">Buat tagihan iuran kas otomatis untuk siswa dalam satu kelas atau seluruh kelas sekaligus.</p>

            <form method="POST" action="{{ route('admin.dues.store') }}" class="space-y-6">
                @csrf

                {{-- Target Kelas --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Target Kelas</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer transition text-sm font-semibold"
                               :class="targetType === 'single' ? 'border-indigo-600 bg-indigo-50 text-indigo-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="target_type" value="single" x-model="targetType" class="sr-only">
                            <span class="text-lg">🏫</span>
                            <span>Satu Kelas Tertentu</span>
                        </label>

                        <label class="relative flex items-center justify-center gap-2 p-3.5 border-2 rounded-xl cursor-pointer transition text-sm font-semibold"
                               :class="targetType === 'all' ? 'border-indigo-600 bg-indigo-50 text-indigo-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="target_type" value="all" x-model="targetType" class="sr-only">
                            <span class="text-lg">🌐</span>
                            <span>Seluruh Kelas ({{ $classes->count() }} Kelas)</span>
                        </label>
                    </div>
                </div>

                {{-- Pilih Kelas jika single --}}
                <div x-show="targetType === 'single'" x-transition>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih Kelas <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" name="class_id"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('class_id') border-red-400 @enderror">
                        <option value="">— Pilih Kelas —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }} ({{ $class->academic_year }}) — {{ $class->students_count }} Siswa
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Kategori Kas --}}
                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori Kas <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('category_id') border-red-400 @enderror">
                        <option value="">— Pilih Kategori Pemasukan —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Judul Tagihan --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Judul Tagihan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('title') border-red-400 @enderror"
                           placeholder="Contoh: Iuran Kas Bulan Oktober 2026">
                    @error('title')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Nominal per Siswa --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Nominal per Siswa (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', 20000) }}" min="1" step="0.01" required
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-semibold @error('amount') border-red-400 @enderror">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jatuh Tempo --}}
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Batas Jatuh Tempo <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-t')) }}" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('due_date') border-red-400 @enderror">
                        @error('due_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="p-4 bg-indigo-50 rounded-xl border border-indigo-100 text-xs text-indigo-800 leading-relaxed">
                    💡 <strong>Catatan:</strong> Tagihan massal akan otomatis berlaku untuk seluruh siswa di kelas target. Setiap pembayaran yang dilakukan siswa (baik lunas maupun cicilan) dapat langsung dicatat oleh petugas/kasir.
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        ⚡ Generate Tagihan Massal
                    </button>
                    <a href="{{ route('admin.dues.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
