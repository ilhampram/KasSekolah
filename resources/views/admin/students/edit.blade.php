@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('header', 'Edit Siswa')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.students.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Daftar Siswa</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Edit Siswa: {{ $student->name }}</h3>

            <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $student->name) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('name') border-red-400 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nisn" class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                    <input type="text" id="nisn" name="nisn" value="{{ old('nisn', $student->nisn) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('nisn') border-red-400 @enderror">
                    @error('nisn')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $student->email) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('email') border-red-400 @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <select id="class_id" name="class_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('class_id') border-red-400 @enderror">
                        <option value="">— Pilih Kelas —</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->class_name }} ({{ $class->academic_year }})
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100 text-sm text-blue-700">
                    💡 Password tidak ditampilkan di sini. Gunakan tombol <strong>🔑 Reset Password</strong> di halaman daftar siswa untuk mereset password ke default.
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Update
                    </button>
                    <a href="{{ route('admin.students.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
