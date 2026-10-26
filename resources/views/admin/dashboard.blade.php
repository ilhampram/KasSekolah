@extends('layouts.app')

@section('title', 'Dashboard Analytics')
@section('header', 'Dashboard Analytics & Keuangan')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Greeting & Overview Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-black text-gray-800">Selamat Datang, {{ auth()->user()->name }} 👋</h2>
            <p class="text-gray-500 text-sm mt-1">Ringkasan performa keuangan kas sekolah & analitika arus kas.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.transactions.create', ['type' => 'out']) }}"
               class="px-3.5 py-2 bg-rose-600 text-white text-xs font-bold rounded-lg hover:bg-rose-700 transition flex items-center gap-1.5 shadow-sm">
                <span>💸</span> + Catat Pengeluaran
            </a>
            <a href="{{ route('admin.transactions.create', ['type' => 'in']) }}"
               class="px-3.5 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                <span>💰</span> + Catat Pemasukan
            </a>
            <a href="{{ route('admin.reports.index') }}"
               class="px-3.5 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition flex items-center gap-1.5 shadow-sm">
                <span>📄</span> Laporan Keuangan
            </a>
        </div>
    </div>

    {{-- Widget KPI (Section 1) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Saldo Kas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Total Saldo Kas</span>
                <span class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-lg">🏦</span>
            </div>
            <p class="text-2xl font-black {{ $totalBalance >= 0 ? 'text-indigo-900' : 'text-rose-600' }} mt-2">
                Rp {{ number_format($totalBalance, 0, ',', '.') }}
            </p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between pt-2 border-t border-gray-100">
                <span>Total Pemasukan:</span>
                <span class="font-semibold text-emerald-600">Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Pemasukan Bulan Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-600">Pemasukan Bulan Ini</span>
                <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg">💰</span>
            </div>
            <p class="text-2xl font-black text-emerald-600 mt-2">
                Rp {{ number_format($incomeThisMonth, 0, ',', '.') }}
            </p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between pt-2 border-t border-gray-100">
                <a href="{{ route('admin.transactions.create', ['type' => 'in']) }}" class="font-bold text-emerald-600 hover:underline">
                    + Input Pemasukan →
                </a>
                <span class="font-medium text-gray-400">{{ now()->translatedFormat('M Y') }}</span>
            </div>
        </div>

        {{-- Pengeluaran Bulan Ini --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-600">Pengeluaran Bulan Ini</span>
                <span class="w-9 h-9 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center text-lg">💸</span>
            </div>
            <p class="text-2xl font-black text-rose-600 mt-2">
                Rp {{ number_format($expenseThisMonth, 0, ',', '.') }}
            </p>
            <div class="mt-2 text-xs text-gray-500 flex items-center justify-between pt-2 border-t border-gray-100">
                <a href="{{ route('admin.transactions.create', ['type' => 'out']) }}" class="font-bold text-rose-600 hover:underline">
                    + Input Pengeluaran →
                </a>
                <span class="font-semibold text-rose-600">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- % Kelancaran Iuran Siswa --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Kelancaran Iuran</span>
                <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-lg">🎯</span>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <p class="text-2xl font-black text-amber-600">{{ $collectionRate }}%</p>
                <span class="text-xs text-gray-400">Tercapai</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $collectionRate }}%"></div>
            </div>
            <div class="mt-2 text-[11px] text-gray-500 flex items-center justify-between pt-1">
                <span>Terkumpul: <strong>Rp {{ number_format($totalCollectedDues, 0, ',', '.') }}</strong></span>
                <span>Target: <strong>Rp {{ number_format($totalTargetAmount, 0, ',', '.') }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Sub Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-indigo-50/60 border border-indigo-100 rounded-xl p-4 flex items-center gap-3">
            <span class="text-2xl">👥</span>
            <div>
                <p class="text-xs text-gray-500 font-medium">Siswa Terdaftar</p>
                <p class="text-base font-bold text-gray-800">{{ $totalStudents }} Siswa ({{ $totalClasses }} Kelas)</p>
            </div>
        </div>

        <div class="bg-amber-50/60 border border-amber-100 rounded-xl p-4 flex items-center gap-3">
            <span class="text-2xl">⏳</span>
            <div>
                <p class="text-xs text-gray-500 font-medium">Menunggu Verifikasi</p>
                <p class="text-base font-bold text-amber-700">{{ $pendingPayments }} Pembayaran Transfer</p>
            </div>
        </div>

        <div class="bg-emerald-50/60 border border-emerald-100 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">⚡</span>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Kasir Cepat</p>
                    <p class="text-xs text-emerald-800 font-semibold">Catat Penerimaan Kas</p>
                </div>
            </div>
            <a href="{{ route('admin.payments.create') }}"
               class="px-3 py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition">
                Buka Kasir
            </a>
        </div>
    </div>

    {{-- Charts Section (Section 2) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Chart 1: Arus Kas Bulanan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Tren Arus Kas Bulanan</h3>
                    <p class="text-xs text-gray-400">Pemasukan vs Pengeluaran 6 Bulan Terakhir</p>
                </div>
                <span class="text-xs text-indigo-600 bg-indigo-50 font-semibold px-2 py-1 rounded-md">
                    Bulanan
                </span>
            </div>
            <div class="relative h-72">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        {{-- Chart 2: Perbandingan Kas Per Kelas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Perbandingan Kas Per Kelas</h3>
                    <p class="text-xs text-gray-400">Akumulasi Pemasukan & Pengeluaran Per Kelas</p>
                </div>
                <span class="text-xs text-indigo-600 bg-indigo-50 font-semibold px-2 py-1 rounded-md">
                    Per Kelas
                </span>
            </div>
            <div class="relative h-72">
                <canvas id="classCashChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Tabel Aktivitas Transaksi Terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm">Aktivitas Transaksi Kas Terbaru</h3>
                <p class="text-xs text-gray-400">Mutasi kas masuk dan keluar terakhir dicatat.</p>
            </div>
            <a href="{{ route('admin.transactions.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                Lihat Buku Kas Lengkap →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal & No. TRX</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori & Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipe</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentTransactions as $trx)
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
                                <p class="font-medium text-gray-800 truncate">{{ $trx->title }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($trx->type === 'in')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ＋ Masuk
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        － Keluar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-black {{ $trx->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.transactions.show', $trx) }}"
                                   class="px-2.5 py-1 text-xs bg-gray-100 text-gray-700 font-medium rounded-md hover:bg-gray-200 transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                Belum ada transaksi kas yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js Initialization --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // ─── Chart 1: Arus Kas Bulanan ────────────────────────
            const ctxCashflow = document.getElementById('cashflowChart').getContext('2d');
            new Chart(ctxCashflow, {
                type: 'bar',
                data: {
                    labels: @js($monthlyLabels),
                    datasets: [
                        {
                            label: 'Pemasukan (Rp)',
                            data: @js($monthlyIncomeData),
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran (Rp)',
                            data: @js($monthlyExpenseData),
                            backgroundColor: 'rgba(244, 63, 94, 0.85)',
                            borderColor: 'rgb(244, 63, 94)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // ─── Chart 2: Perbandingan Kas Per Kelas ───────────────
            const ctxClass = document.getElementById('classCashChart').getContext('2d');
            new Chart(ctxClass, {
                type: 'bar',
                data: {
                    labels: @js($classLabels),
                    datasets: [
                        {
                            label: 'Pemasukan (Rp)',
                            data: @js($classIncomeData),
                            backgroundColor: 'rgba(99, 102, 241, 0.85)',
                            borderColor: 'rgb(99, 102, 241)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran (Rp)',
                            data: @js($classExpenseData),
                            backgroundColor: 'rgba(239, 68, 68, 0.85)',
                            borderColor: 'rgb(239, 68, 68)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection

