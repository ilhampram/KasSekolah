@extends('layouts.app')

@section('title', 'Master Kelas')
@section('header', 'Master Kelas')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Kelas</h2>
            <p class="text-sm text-gray-500">Kelola data kelas yang terikat dengan tahun ajaran.</p>
        </div>
        <a href="{{ route('admin.classes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <span>＋</span> Tambah Kelas
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Cari nama kelas atau wali kelas...">
            </div>
            <div>
                <select name="academic_year_id"
                        class="w-full sm:w-auto rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Tahun Ajaran</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->full_name }} {{ $year->is_active ? '★' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                🔍 Cari
            </button>
            @if(request('search') || request('academic_year_id'))
                <a href="{{ route('admin.classes.index') }}"
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Siswa</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($classes as $i => $class)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $classes->firstItem() + $i }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $class->class_name }}</td>
                            <td class="px-6 py-4">
                                @if($class->academicYear)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class->academicYear->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $class->academicYear->full_name }}
                                        @if($class->academicYear->is_active)
                                            <span class="text-xs">★</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">{{ $class->academic_year ?? '-' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $class->homeroom_teacher }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $class->students_count }} siswa
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.classes.edit', $class) }}"
                                       class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 font-medium rounded-md hover:bg-amber-100 transition">
                                        ✏️ Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus kelas {{ $class->class_name }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs bg-red-50 text-red-600 font-medium rounded-md hover:bg-red-100 transition">
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
                                <p>Belum ada data kelas.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($classes->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $classes->links() }}
            </div>
        @endif
    </div>
@endsection
