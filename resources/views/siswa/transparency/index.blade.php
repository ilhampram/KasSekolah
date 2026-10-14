@extends('layouts.app')

@section('title', 'Transparansi Kas')
@section('header', 'Buku Kas Transparan')

@section('content')
    <div x-data="{ modalOpen: false, modalImage: '', modalTitle: '', isPdf: false }">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Transparansi Arus Kas</h2>
                <p class="text-sm text-gray-500">
                    Laporan keuangan terbuka kas kelas & sekolah. Seluruh siswa dapat memeriksa setiap pemasukan, pengeluaran, dan bukti nota secara akuntabel.
                </p>
            </div>

            {{-- Scope Switcher --}}
            <div class="inline-flex bg-gray-200/80 p-1 rounded-xl text-xs font-bold">
                <a href="{{ route('siswa.transparency.index', array_merge(request()->except('scope', 'page'), ['scope' => 'class'])) }}"
                   class="px-3.5 py-1.5 rounded-lg transition {{ $scope === 'class' ? 'bg-white text-indigo-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    🏫 Kas Kelas ({{ $user->class ? $user->class->class_name : 'Saya' }})
                </a>
                <a href="{{ route('siswa.transparency.index', array_merge(request()->except('scope', 'page'), ['scope' => 'all'])) }}"
                   class="px-3.5 py-1.5 rounded-lg transition {{ $scope === 'all' ? 'bg-white text-indigo-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                    🌐 Seluruh Kas Sekolah
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-2xl">💰</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pemasukan ({{ $scope === 'class' ? 'Kelas' : 'Sekolah' }})</p>
                        <p class="text-xl font-bold text-emerald-600 mt-0.5">Rp {{ number_format($totalIncome, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-rose-100 flex items-center justify-center text-2xl">💸</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pengeluaran</p>
                        <p class="text-xl font-bold text-rose-600 mt-0.5">Rp {{ number_format($totalExpense, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center text-2xl">🏦</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Sisa Saldo Kas</p>
                        <p class="text-xl font-bold {{ $balance >= 0 ? 'text-indigo-800' : 'text-rose-600' }} mt-0.5">
                            Rp {{ number_format($balance, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="scope" value="{{ $scope }}">

                <div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Cari transaksi...">
                </div>

                <div>
                    <select name="type"
                            class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">Semua Arus Kas</option>
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
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                        🔍 Cari
                    </button>
                    @if(request()->hasAny(['search', 'type', 'category_id', 'start_date', 'end_date']))
                        <a href="{{ route('siswa.transparency.index', ['scope' => $scope]) }}"
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
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal & No. TRX</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori & Lingkup</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Bukti Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800 text-xs">{{ $trx->transaction_date->format('d M Y') }}</p>
                                    <p class="font-mono text-[11px] text-gray-400 mt-0.5">{{ $trx->transaction_number }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800">{{ $trx->category->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $trx->schoolClass ? $trx->schoolClass->class_name : 'Kas Umum Sekolah' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 max-w-sm">
                                    <p class="font-medium text-gray-800">{{ $trx->title }}</p>
                                    @if($trx->notes)
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-1 italic">{{ $trx->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($trx->type === 'in')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            ＋ Pemasukan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                            － Pengeluaran
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-black {{ $trx->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($trx->proof_file)
                                        @php
                                            $ext = strtolower(pathinfo($trx->proof_file, PATHINFO_EXTENSION));
                                            $fileUrl = asset('storage/' . $trx->proof_file);
                                            $isPdf = $ext === 'pdf';
                                        @endphp

                                        @if($isPdf)
                                            <a href="{{ $fileUrl }}" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md font-semibold transition">
                                                📄 Dokumen PDF
                                            </a>
                                        @else
                                            <button type="button"
                                                    @click="modalOpen = true; modalImage = '{{ $fileUrl }}'; modalTitle = '{{ addslashes($trx->title) }}'; isPdf = false"
                                                    class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-md font-semibold transition">
                                                🖼️ Lihat Nota
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <p class="text-2xl mb-1">📭</p>
                                    <p class="font-medium">Tidak ada transaksi kas yang ditemukan.</p>
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

        {{-- Modal Preview Foto Nota (Alpine.js) --}}
        <div x-show="modalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/75"
             x-transition>
            <div class="bg-white rounded-2xl max-w-2xl w-full overflow-hidden shadow-2xl"
                 @click.away="modalOpen = false">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm" x-text="modalTitle"></h4>
                        <p class="text-xs text-gray-400">Lampiran Bukti Transaksi Resmi</p>
                    </div>
                    <button @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl font-bold leading-none">&times;</button>
                </div>
                <div class="p-6 bg-gray-50 flex items-center justify-center max-h-[75vh] overflow-y-auto">
                    <img :src="modalImage" alt="Bukti Transaksi" class="max-h-[65vh] w-auto rounded-lg shadow-sm object-contain">
                </div>
                <div class="px-6 py-3 border-t border-gray-100 bg-white flex justify-end gap-2">
                    <a :href="modalImage" target="_blank" class="px-4 py-2 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-100 transition">
                        Buka di Tab Baru
                    </a>
                    <button @click="modalOpen = false" class="px-4 py-2 bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-300 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
