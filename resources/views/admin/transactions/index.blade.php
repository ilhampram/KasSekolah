@extends('layouts.app')

@section('title', 'Transaksi Kas')
@section('header', 'Transaksi Kas')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Buku Transaksi Kas</h2>
            <p class="text-sm text-gray-500">Pencatatan seluruh pemasukan dan pengeluaran kas sekolah/kelas.</p>
        </div>
        <a href="{{ route('admin.transactions.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
            <span>＋</span> Catat Transaksi Baru
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-2xl">💰</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pemasukan</p>
                    <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-rose-100 flex items-center justify-center text-2xl">💸</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pengeluaran</p>
                    <p class="text-xl font-bold text-rose-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center text-2xl">🏦</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Saldo Kas Bersih</p>
                    <p class="text-xl font-bold {{ $balance >= 0 ? 'text-indigo-700' : 'text-rose-600' }}">
                        Rp {{ number_format($balance, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Cari No. TRX, judul, catatan...">
            </div>

            <div>
                <select name="type"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>💰 Pemasukan (In)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>💸 Pengeluaran (Out)</option>
                </select>
            </div>

            <div>
                <select name="category_id"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->type === 'in' ? '[+]' : '[-]' }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    🔍 Filter
                </button>
                @if(request()->hasAny(['search', 'type', 'category_id', 'class_id', 'start_date', 'end_date']))
                    <a href="{{ route('admin.transactions.index') }}"
                       class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. TRX & Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori & Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Bukti</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs font-semibold text-gray-800">{{ $trx->transaction_number }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $trx->transaction_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $trx->category->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $trx->schoolClass ? $trx->schoolClass->class_name : 'Kas Umum / Sekolah' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <p class="font-medium text-gray-800 truncate" title="{{ $trx->title }}">{{ $trx->title }}</p>
                                @if($trx->payment)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded mt-0.5">
                                        🧾 Pembayaran Siswa
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($trx->type === 'in')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ＋ Pemasukan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        － Pengeluaran
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $trx->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($trx->proof_file)
                                    <a href="{{ asset('storage/' . $trx->proof_file) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 font-medium bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md transition"
                                       title="Lihat Bukti Transaksi">
                                        📎 Bukti
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.transactions.show', $trx) }}"
                                       class="px-2.5 py-1.5 text-xs bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition"
                                       title="Detail">
                                        👁️ Detail
                                    </a>

                                    @if(! $trx->payment_id)
                                        <a href="{{ route('admin.transactions.edit', $trx) }}"
                                           class="px-2.5 py-1.5 text-xs bg-amber-50 text-amber-700 font-medium rounded-md hover:bg-amber-100 transition"
                                           title="Edit">
                                            ✏️
                                        </a>

                                        <form method="POST" action="{{ route('admin.transactions.destroy', $trx) }}"
                                              onsubmit="return confirm('Hapus transaksi {{ $trx->transaction_number }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-2.5 py-1.5 text-xs bg-red-50 text-red-600 font-medium rounded-md hover:bg-red-100 transition"
                                                    title="Hapus">
                                                🗑️
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <p class="text-2xl mb-1">📭</p>
                                <p class="font-medium">Belum ada transaksi kas yang dicatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
@endsection
