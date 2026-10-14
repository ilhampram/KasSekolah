@extends('layouts.app')

@section('title', 'Laporan Keuangan Kas')
@section('header', 'Laporan Keuangan & Rekapitulasi')

@section('content')
    {{-- Header & Export Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Laporan Keuangan & Rekapitulasi Kas</h2>
            <p class="text-sm text-gray-500">Laporan mutasi arus kas lengkap dengan saldo awal/akhir & rekap tunggakan siswa.</p>
        </div>

        {{-- Export Buttons --}}
        <div class="flex items-center gap-2">
            @if($tab === 'transactions')
                <a href="{{ route('admin.reports.export-excel', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                    <span>📗</span> Unduh Excel (.xlsx)
                </a>
                <a href="{{ route('admin.reports.export-pdf', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                    <span>📕</span> Unduh PDF (.pdf)
                </a>
            @else
                <a href="{{ route('admin.reports.export-dues-excel', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                    <span>📗</span> Unduh Excel Tunggakan
                </a>
                <a href="{{ route('admin.reports.export-dues-pdf', request()->all()) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                    <span>📕</span> Unduh PDF Tunggakan
                </a>
            @endif
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex border-b border-gray-200 mb-6 gap-2">
        <a href="{{ route('admin.reports.index', array_merge(request()->except('tab'), ['tab' => 'transactions'])) }}"
           class="px-5 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2
                  {{ $tab === 'transactions' ? 'border-indigo-600 text-indigo-700 bg-white rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <span>📊</span> Buku Mutasi Kas & Saldo
        </a>
        <a href="{{ route('admin.reports.index', array_merge(request()->except('tab'), ['tab' => 'dues'])) }}"
           class="px-5 py-3 text-sm font-bold border-b-2 transition flex items-center gap-2
                  {{ $tab === 'dues' ? 'border-indigo-600 text-indigo-700 bg-white rounded-t-lg' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            <span>👥</span> Rekapitulasi Iuran & Tunggakan Siswa
        </a>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            @if($tab === 'transactions')
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}"
                           class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                           class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Filter Kelas</label>
                <select name="class_id"
                        class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ $classId == $c->id ? 'selected' : '' }}>{{ $c->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori Kas</label>
                <select name="category_id"
                        class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                            {{ $cat->type === 'in' ? '[+]' : '[-]' }} {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="flex-1 py-2 px-4 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                    🔍 Terapkan Filter
                </button>
                <a href="{{ route('admin.reports.index', ['tab' => $tab]) }}"
                   class="py-2 px-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($tab === 'transactions')
        {{-- Rekapitulasi Otomatis Box (Section 3) --}}
        <div class="bg-gradient-to-br from-indigo-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-6 sm:p-7 mb-6 shadow-sm border border-indigo-800">
            <div class="flex items-center justify-between pb-4 border-b border-indigo-800/80 mb-5">
                <div>
                    <h3 class="text-base font-bold tracking-tight">Rekapitulasi Arus Kas Otomatis</h3>
                    <p class="text-xs text-indigo-300">
                        Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
                    </p>
                </div>
                <span class="text-xs bg-indigo-800/80 text-indigo-200 px-3 py-1 rounded-full font-mono">
                    Otomatis Terakumulasi
                </span>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Saldo Awal --}}
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
                    <p class="text-xs text-indigo-200 font-medium">Saldo Awal</p>
                    <p class="text-xl font-black mt-1">Rp {{ number_format($summary['saldo_awal'], 0, ',', '.') }}</p>
                    <p class="text-[10px] text-indigo-300 mt-1">Akumulasi sebelum tanggal mulai</p>
                </div>

                {{-- Mutasi Masuk --}}
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
                    <p class="text-xs text-emerald-300 font-medium">Mutasi Masuk (+)</p>
                    <p class="text-xl font-black text-emerald-400 mt-1">Rp {{ number_format($summary['mutasi_masuk'], 0, ',', '.') }}</p>
                    <p class="text-[10px] text-indigo-300 mt-1">Pemasukan dalam periode</p>
                </div>

                {{-- Mutasi Keluar --}}
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10">
                    <p class="text-xs text-rose-300 font-medium">Mutasi Keluar (-)</p>
                    <p class="text-xl font-black text-rose-400 mt-1">Rp {{ number_format($summary['mutasi_keluar'], 0, ',', '.') }}</p>
                    <p class="text-[10px] text-indigo-300 mt-1">Pengeluaran dalam periode</p>
                </div>

                {{-- Saldo Akhir --}}
                <div class="bg-indigo-500/30 backdrop-blur rounded-xl p-4 border border-indigo-400/30">
                    <p class="text-xs text-amber-300 font-bold uppercase tracking-wider">Saldo Akhir</p>
                    <p class="text-2xl font-black text-amber-300 mt-1">Rp {{ number_format($summary['saldo_akhir'], 0, ',', '.') }}</p>
                    <p class="text-[10px] text-indigo-200 mt-1">Saldo Awal + Masuk - Keluar</p>
                </div>
            </div>
        </div>

        {{-- Tabel Mutasi Kas dengan Saldo Berjalan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-sm">Rincian Buku Kas Mutasi Berjalan ({{ count($transactions) }} Transaksi)</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal & No. TRX</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori & Lingkup</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Pemasukan (Rp)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengeluaran (Rp)</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo Berjalan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {{-- Baris Saldo Awal --}}
                        <tr class="bg-gray-50/70 font-semibold text-xs">
                            <td colspan="5" class="px-6 py-3 text-gray-600 italic">
                                📌 Saldo Awal per {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-gray-800">
                                Rp {{ number_format($summary['saldo_awal'], 0, ',', '.') }}
                            </td>
                        </tr>

                        @forelse($transactions as $row)
                            @php $trx = $row['model']; @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-bold text-gray-800 text-xs">{{ $trx->transaction_date->format('d M Y') }}</p>
                                    <p class="font-mono text-[11px] text-gray-400 mt-0.5">{{ $trx->transaction_number }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-800">{{ $trx->category->name }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $trx->schoolClass ? $trx->schoolClass->class_name : 'Kas Umum' }}</p>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <p class="font-medium text-gray-800">{{ $trx->title }}</p>
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $trx->type === 'in' ? 'text-emerald-600' : 'text-gray-300' }}">
                                    {{ $trx->type === 'in' ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold {{ $trx->type === 'out' ? 'text-rose-600' : 'text-gray-300' }}">
                                    {{ $trx->type === 'out' ? 'Rp ' . number_format($trx->amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-right font-black text-indigo-900">
                                    Rp {{ number_format($row['running_balance'], 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    Tidak ada transaksi kas dalam periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        {{-- TAB 2: Rekapitulasi Tunggakan Siswa --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <p class="text-xs text-gray-500 font-medium">Total Target Tagihan</p>
                <p class="text-xl font-black text-gray-800 mt-1">Rp {{ number_format($duesData['summary']['total_target'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <p class="text-xs text-emerald-600 font-medium">Total Terkumpul</p>
                <p class="text-xl font-black text-emerald-600 mt-1">Rp {{ number_format($duesData['summary']['total_paid'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <p class="text-xs text-rose-600 font-medium">Total Sisa Tunggakan</p>
                <p class="text-xl font-black text-rose-600 mt-1">Rp {{ number_format($duesData['summary']['total_debt'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
                <p class="text-xs text-indigo-600 font-medium">Rasio Lunas</p>
                <p class="text-xl font-black text-indigo-900 mt-1">
                    {{ $duesData['summary']['lunas_count'] }} <span class="text-xs font-normal text-gray-400">/ {{ $duesData['summary']['lunas_count'] + $duesData['summary']['belum_count'] }}</span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800 text-sm">Daftar Rekap Tunggakan Seluruh Siswa</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan Kas</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibayar</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Tunggakan</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($duesData['items'] as $i => $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 font-bold text-gray-800">{{ $item['student_name'] }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ $item['nisn'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-700">{{ $item['class_name'] }}</td>
                                <td class="px-6 py-4 text-gray-800">{{ $item['bill_title'] }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-gray-800">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-bold text-emerald-600">Rp {{ number_format($item['paid'], 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-right font-bold {{ $item['remaining'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                    Rp {{ number_format($item['remaining'], 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item['status'] === 'lunas')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                            ✓ Lunas
                                        </span>
                                    @elseif($item['status'] === 'cicilan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                            ⚡ Cicilan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700">
                                            Belum Bayar
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-400">
                                    Tidak ada data tagihan yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
