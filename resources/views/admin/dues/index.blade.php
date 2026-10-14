@extends('layouts.app')

@section('title', 'Tagihan Siswa')
@section('header', 'Tagihan Siswa')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Manajemen Tagihan Kas Siswa</h2>
            <p class="text-sm text-gray-500">Kelola dan pantau iuran kas bulanan dan kegiatan per kelas.</p>
        </div>
        <a href="{{ route('admin.dues.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <span>＋</span> Generate Tagihan Massal
        </a>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Cari judul tagihan atau nama kelas...">
            </div>

            <div>
                <select name="class_id"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->class_name }} ({{ $class->academic_year }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <select name="category_id"
                        class="flex-1 rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>

                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    🔍 Cari
                </button>
                @if(request()->hasAny(['search', 'class_id', 'category_id']))
                    <a href="{{ route('admin.dues.index') }}"
                       class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Dues Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Judul Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal / Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Terkumpul</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bills as $i => $bill)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $bills->firstItem() + $i }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.dues.show', $bill) }}" class="font-bold text-gray-800 hover:text-indigo-600 transition">
                                    {{ $bill->title }}
                                </a>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $bill->payments_count }} riwayat pembayaran</p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $bill->schoolClass->class_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $bill->category->name }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                Rp {{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                {{ $bill->due_date->format('d M Y') }}
                                @if($bill->due_date->isPast())
                                    <span class="block text-[11px] text-red-500 font-medium">Lewat Jatuh Tempo</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                Rp {{ number_format($bill->totalCollected(), 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.dues.show', $bill) }}"
                                       class="px-3 py-1.5 text-xs bg-indigo-50 text-indigo-700 font-medium rounded-md hover:bg-indigo-100 transition"
                                       title="Pantau & Catat Kasir">
                                        👥 Pantau Siswa
                                    </a>
                                    <a href="{{ route('admin.dues.edit', $bill) }}"
                                       class="px-2.5 py-1.5 text-xs bg-amber-50 text-amber-700 font-medium rounded-md hover:bg-amber-100 transition"
                                       title="Edit">
                                        ✏️
                                    </a>
                                    <form method="POST" action="{{ route('admin.dues.destroy', $bill) }}"
                                          onsubmit="return confirm('Hapus tagihan {{ $bill->title }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="px-2.5 py-1.5 text-xs bg-red-50 text-red-600 font-medium rounded-md hover:bg-red-100 transition"
                                                title="Hapus">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-2xl mb-1">📭</p>
                                <p class="font-medium">Belum ada tagihan iuran siswa.</p>
                                <a href="{{ route('admin.dues.create') }}" class="inline-block mt-3 text-sm text-indigo-600 font-semibold hover:underline">
                                    ＋ Generate Tagihan Massal Sekarang
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bills->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $bills->links() }}
            </div>
        @endif
    </div>
@endsection
