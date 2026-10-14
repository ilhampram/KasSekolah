@extends('layouts.app')

@section('title', 'Tahun Ajaran')
@section('header', 'Tahun Ajaran')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Daftar Tahun Ajaran</h2>
            <p class="text-sm text-gray-500">Kelola tahun ajaran dan semester aktif untuk seluruh data kas.</p>
        </div>
        <a href="{{ route('admin.academic-years.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <span>＋</span> Tambah Tahun Ajaran
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Cari tahun ajaran (contoh: 2026/2027)...">
            </div>
            <div>
                <select name="status"
                        class="w-full sm:w-auto rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                🔍 Cari
            </button>
            @if(request('search') || request('status'))
                <a href="{{ route('admin.academic-years.index') }}"
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
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Semester</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Kelas</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($academicYears as $i => $ay)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $academicYears->firstItem() + $i }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $ay->year }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $ay->semester }}</td>
                            <td class="px-6 py-4">
                                @if($ay->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        ● Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                    {{ $ay->classes_count }} Kelas
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(! $ay->is_active)
                                        <form method="POST" action="{{ route('admin.academic-years.activate', $ay) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs bg-emerald-50 text-emerald-700 font-medium rounded-md hover:bg-emerald-100 transition"
                                                    onclick="return confirm('Aktifkan tahun ajaran {{ $ay->full_name }}?')">
                                                ⚡ Aktifkan
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.academic-years.edit', $ay) }}"
                                       class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 font-medium rounded-md hover:bg-amber-100 transition">
                                        ✏️ Edit
                                    </a>

                                    @if(! $ay->is_active)
                                        <form method="POST" action="{{ route('admin.academic-years.destroy', $ay) }}"
                                              onsubmit="return confirm('Hapus tahun ajaran {{ $ay->full_name }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs bg-red-50 text-red-600 font-medium rounded-md hover:bg-red-100 transition">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-lg mb-1">📭</p>
                                <p>Belum ada data tahun ajaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($academicYears->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $academicYears->links() }}
            </div>
        @endif
    </div>
@endsection
