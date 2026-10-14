@extends('layouts.app')

@section('title', 'Master Siswa')
@section('header', 'Master Siswa')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Siswa</h2>
            <p class="text-sm text-gray-500">Kelola data siswa, assignment kelas, dan reset password.</p>
        </div>
        <a href="{{ route('admin.students.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <span>＋</span> Tambah Siswa
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Cari nama, email, NISN...">
            </div>
            <div>
                <select name="class_id"
                        class="w-full sm:w-auto rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->class_name }} ({{ $class->academic_year }})
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                🔍 Cari
            </button>
            @if(request('search') || request('class_id'))
                <a href="{{ route('admin.students.index') }}"
                   class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition text-center">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $i => $student)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $students->firstItem() + $i }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-800">{{ $student->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $student->nisn ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $student->email }}</td>
                            <td class="px-6 py-4">
                                @if($student->class)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        {{ $student->class->class_name }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2" x-data="{ open: false }">
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                       class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 rounded-md hover:bg-amber-100 transition">
                                        ✏️ Edit
                                    </a>

                                    {{-- Reset Password --}}
                                    <form method="POST" action="{{ route('admin.students.reset-password', $student) }}"
                                          onsubmit="return confirm('Reset password {{ $student->name }} ke default?')">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs bg-blue-50 text-blue-600 rounded-md hover:bg-blue-100 transition">
                                            🔑 Reset
                                        </button>
                                    </form>

                                    {{-- Delete --}}
                                    <form method="POST" action="{{ route('admin.students.destroy', $student) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus siswa {{ $student->name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs bg-red-50 text-red-600 rounded-md hover:bg-red-100 transition">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-lg mb-1">📭</p>
                                <p>Belum ada data siswa.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>
@endsection
