@extends('layouts.app')

@section('title', 'Master Siswa')
@section('header', 'Master Siswa')

@section('content')
    {{-- Header --}}
    <div x-data="{ showImport: false }" class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Daftar Siswa</h2>
                <p class="text-sm text-gray-500">Kelola data siswa, assignment kelas, dan reset password.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="showImport = !showImport"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 text-emerald-700 border border-emerald-300 text-sm font-semibold rounded-lg hover:bg-emerald-100 transition">
                    <span>📥</span> Import Excel / CSV
                </button>
                <a href="{{ route('admin.students.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <span>＋</span> Tambah Siswa
                </a>
            </div>
        </div>

        {{-- Import Box --}}
        <div x-show="showImport" x-transition class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-5 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-bold text-emerald-900 text-sm">Upload File Excel / CSV Siswa</h3>
                    <p class="text-xs text-emerald-700">Mendukung file <strong>.xlsx, .xls, .csv</strong>. Kolom: <code>Nama Siswa, NISN, Email (opsional)</code>.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.students.template', ['format' => 'xlsx']) }}"
                       class="text-xs px-2.5 py-1.5 bg-emerald-700 text-white font-semibold rounded-lg hover:bg-emerald-800 transition flex items-center gap-1 shadow-sm">
                        📊 Unduh Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('admin.students.template', ['format' => 'csv']) }}"
                       class="text-xs text-emerald-800 underline hover:text-emerald-950 font-medium">
                        (atau .csv)
                    </a>
                </div>
            </div>

            <form action="{{ route('admin.students.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-3">
                @csrf
                @if(!auth()->user()->isBendaharaKelas())
                    <select name="class_id" required class="w-full sm:w-56 rounded-lg border-gray-300 text-sm px-3 py-2 border bg-white focus:ring-2 focus:ring-emerald-500 outline-none">
                        <option value="">Pilih Kelas Tujuan...</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->class_name }}</option>
                        @endforeach
                    </select>
                @endif

                <input type="file" name="file" accept=".xlsx,.xls,.csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv" required
                       class="w-full sm:flex-1 text-xs file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">

                <button type="submit"
                        class="w-full sm:w-auto px-5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition">
                    Mulai Impor
                </button>
            </form>
        </div>
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

    {{-- Active Class Filter Alert --}}
    @if(request('class_id'))
        @php
            $activeClass = $classes->firstWhere('id', request('class_id'));
        @endphp
        @if($activeClass)
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl text-xs text-blue-800 flex items-center justify-between">
                <span>Filter aktif: Menampilkan siswa kelas <strong>{{ $activeClass->class_name }}</strong> (Total: {{ $students->total() }} siswa)</span>
                <a href="{{ route('admin.students.index') }}" class="font-bold underline text-blue-900 hover:text-blue-950">
                    ✕ Tampilkan Semua Siswa
                </a>
            </div>
        @endif
    @endif

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
