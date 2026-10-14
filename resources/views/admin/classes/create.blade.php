@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('header', 'Tambah Kelas')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.classes.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Daftar Kelas</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Form Tambah Kelas</h3>

            <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="class_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kelas</label>
                    <input type="text" id="class_name" name="class_name" value="{{ old('class_name') }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('class_name') border-red-400 @enderror"
                           placeholder="Contoh: X IPA 1">
                    @error('class_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                    <select id="academic_year_id" name="academic_year_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('academic_year_id') border-red-400 @enderror">
                        <option value="">— Pilih Tahun Ajaran —</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ old('academic_year_id', $activeYear?->id) == $ay->id ? 'selected' : '' }}>
                                {{ $ay->full_name }} {{ $ay->is_active ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="homeroom_teacher" class="block text-sm font-medium text-gray-700 mb-1">Wali Kelas</label>
                    <input type="text" id="homeroom_teacher" name="homeroom_teacher" value="{{ old('homeroom_teacher') }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('homeroom_teacher') border-red-400 @enderror"
                           placeholder="Nama lengkap wali kelas">
                    @error('homeroom_teacher')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Simpan
                    </button>
                    <a href="{{ route('admin.classes.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
