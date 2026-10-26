@extends('layouts.app')

@section('title', 'Dashboard Siswa')
@section('header', 'Portal Siswa')

@section('content')
    {{-- Greeting & Student Info Banner --}}
    <div class="bg-gradient-to-r from-indigo-700 via-indigo-800 to-indigo-900 rounded-2xl text-white p-6 sm:p-8 mb-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="inline-block px-3 py-1 bg-indigo-500/30 text-indigo-100 text-xs font-semibold rounded-full uppercase tracking-wider mb-2">
                    Portal Keuangan Siswa
                </span>
                <h2 class="text-2xl font-black">Halo, {{ $user->name }} 👋</h2>
                <p class="text-indigo-200 text-xs sm:text-sm mt-1">
                    NISN: <span class="font-mono font-semibold">{{ $user->nisn ?? '-' }}</span> |
                    Kelas: <span class="font-semibold">{{ $user->class ? $user->class->class_name . ' (' . $user->class->academic_year . ')' : 'Belum Ditentukan' }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('siswa.payments.create') }}"
                   class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center gap-1.5">
                    <span>📤</span> Upload Bukti Transfer
                </a>
            </div>
        </div>
    </div>

    {{-- Ringkasan Tagihan Pribadi (Section 1) --}}
    <div class="mb-8">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-3">Ringkasan Tagihan Pribadi</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Tagihan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">📋</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Total Tagihan Kelas</p>
                        <p class="text-lg font-bold text-gray-800 mt-0.5">Rp {{ number_format($totalBillAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Total Sudah Dibayar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">💰</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Sudah Dibayar</p>
                        <p class="text-lg font-bold text-emerald-600 mt-0.5">Rp {{ number_format($totalPaidAmount, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Sisa Tunggakan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg {{ $remainingDebt > 0 ? 'bg-rose-50 text-rose-600' : 'bg-gray-50 text-gray-400' }} flex items-center justify-center text-xl">
                        {{ $remainingDebt > 0 ? '⚠️' : '✓' }}
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Sisa Tunggakan</p>
                        <p class="text-lg font-bold {{ $remainingDebt > 0 ? 'text-rose-600' : 'text-gray-700' }} mt-0.5">
                            Rp {{ number_format($remainingDebt, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Status Pelunasan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xl">🎓</div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Tagihan Lunas</p>
                        <p class="text-lg font-bold text-blue-600 mt-0.5">
                            {{ $lunasCount }} <span class="text-xs font-normal text-gray-500">/ {{ $bills->count() }} Tagihan</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Widget Saldo Kas Transparansi (Section 2) --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Transparansi Saldo Kas Terkini</h3>
            <a href="{{ route('siswa.transparency.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                Lihat Rincian Buku Kas Transparan →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Kas Kelas Siswa --}}
            <div class="bg-gradient-to-br from-indigo-50/70 via-white to-blue-50/70 rounded-2xl p-5 border border-indigo-100 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-indigo-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🏫</span>
                        <h4 class="font-bold text-indigo-950 text-sm">
                            Kas Kelas {{ $user->class ? $user->class->class_name : 'Saya' }}
                        </h4>
                    </div>
                    <span class="text-xs text-indigo-600 font-semibold bg-indigo-100/60 px-2 py-0.5 rounded-full">
                        Khusus Kelas
                    </span>
                </div>

                <div class="mt-4 flex items-baseline justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Saldo Kas Kelas Saat Ini</p>
                        <p class="text-2xl font-black {{ $classBalance >= 0 ? 'text-indigo-900' : 'text-rose-600' }} mt-0.5">
                            Rp {{ number_format($classBalance, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-indigo-100/60 text-xs">
                    <div>
                        <span class="text-gray-500 block">Pemasukan Kelas:</span>
                        <strong class="text-emerald-600">Rp {{ number_format($classIncome, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Pengeluaran Kelas:</span>
                        <strong class="text-rose-600">Rp {{ number_format($classExpense, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            {{-- Kas Seluruh Sekolah --}}
            <div class="bg-gradient-to-br from-emerald-50/70 via-white to-teal-50/70 rounded-2xl p-5 border border-emerald-100 shadow-sm">
                <div class="flex items-center justify-between pb-3 border-b border-emerald-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🏛️</span>
                        <h4 class="font-bold text-emerald-950 text-sm">Kas Global Sekolah</h4>
                    </div>
                    <span class="text-xs text-emerald-700 font-semibold bg-emerald-100/60 px-2 py-0.5 rounded-full">
                        Seluruh Kas
                    </span>
                </div>

                <div class="mt-4 flex items-baseline justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium">Saldo Kas Total Sekolah</p>
                        <p class="text-2xl font-black {{ $schoolBalance >= 0 ? 'text-emerald-900' : 'text-rose-600' }} mt-0.5">
                            Rp {{ number_format($schoolBalance, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 mt-4 pt-3 border-t border-emerald-100/60 text-xs">
                    <div>
                        <span class="text-gray-500 block">Total Pemasukan:</span>
                        <strong class="text-emerald-600">Rp {{ number_format($schoolIncome, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Total Pengeluaran:</span>
                        <strong class="text-rose-600">Rp {{ number_format($schoolExpense, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Tagihan Aktif Siswa --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Daftar Tagihan Kas Saya ({{ $bills->count() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal Tagihan</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sudah Dibayar</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Tunggakan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bills as $bill)
                        @php
                            $status = $billStatuses[$bill->id];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $bill->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Jatuh Tempo: {{ $bill->due_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $bill->category->name }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">Rp {{ number_format($status['paid'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-bold {{ $status['remaining'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                Rp {{ number_format($status['remaining'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($status['status'] === 'lunas')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Lunas
                                    </span>
                                @elseif($status['remaining'] <= $status['pending'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800" title="Pembayaran sebesar Rp {{ number_format($status['pending'], 0, ',', '.') }} sedang menunggu verifikasi bendahara">
                                        ⏳ Menunggu Verifikasi
                                    </span>
                                @elseif($status['status'] === 'cicilan')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        ⚡ Cicilan
                                    </span>
                                    @if($status['pending'] > 0)
                                        <span class="block text-[10px] text-amber-600 mt-0.5 font-medium">
                                            (Rp {{ number_format($status['pending'], 0, ',', '.') }} diproses)
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($status['status'] === 'lunas')
                                    <span class="text-xs text-emerald-600 font-semibold">Tuntas</span>
                                @elseif($status['remaining'] <= $status['pending'])
                                    <a href="{{ route('siswa.payments.index', ['status' => 'pending']) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-lg hover:bg-amber-100 transition">
                                        ⏳ Lihat Pengajuan
                                    </a>
                                @else
                                    <a href="{{ route('siswa.payments.create', ['bill_id' => $bill->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">
                                        <span>📤</span> Upload Resi
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada tagihan kas untuk kelas kamu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Pembayaran Terbaru --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Riwayat Pembayaran Terbaru Saya</h3>
            <a href="{{ route('siswa.payments.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                Lihat Semua Riwayat →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Kwitansi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentPayments as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-gray-800">{{ $payment->receipt_number ?: '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $payment->cashBill->title }}</td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 capitalize text-xs text-gray-600">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->status === 'verified')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Verified</span>
                                @elseif($payment->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Menunggu</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($payment->status === 'verified')
                                    <a href="{{ route('siswa.payments.receipt', $payment) }}"
                                       class="px-2.5 py-1.5 text-xs bg-indigo-50 text-indigo-700 font-semibold rounded-md hover:bg-indigo-100 transition inline-flex items-center gap-1">
                                        🧾 Kwitansi
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-6 text-center text-gray-400">
                                Belum ada riwayat pembayaran yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
