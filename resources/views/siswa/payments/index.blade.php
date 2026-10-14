@extends('layouts.app')

@section('title', 'Histori Pembayaran Siswa')
@section('header', 'Histori Pembayaran Saya')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Riwayat Pembayaran Uang Kas</h2>
            <p class="text-sm text-gray-500">Daftar seluruh transaksi pembayaran kas yang telah kamu lakukan.</p>
        </div>
        <a href="{{ route('siswa.payments.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 transition shadow-sm">
            <span>📤</span> Upload Bukti Transfer Baru
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-2xl">💰</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pembayaran Terverifikasi</p>
                    <p class="text-xl font-bold text-emerald-600 mt-0.5">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center text-2xl">⏳</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Menunggu Konfirmasi Bendahara</p>
                    <p class="text-xl font-bold text-yellow-600 mt-0.5">{{ $pendingCount }} Transaksi</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <select name="status"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>✓ Terverifikasi (Sah)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Menunggu Konfirmasi</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>✕ Ditolak</option>
                </select>
            </div>

            <div>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Dari Tanggal">
            </div>

            <div>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="Sampai Tanggal">
            </div>

            <button type="submit"
                    class="px-5 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                Filter
            </button>
            @if(request()->hasAny(['status', 'start_date', 'end_date']))
                <a href="{{ route('siswa.payments.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Payments Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Kwitansi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Dibayar</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Bukti Resi</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-gray-800">
                                {{ $payment->receipt_number ?: '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $payment->cashBill->title }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">Kategori: {{ $payment->cashBill->category->name }}</p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                {{ $payment->payment_date->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded {{ $payment->payment_method === 'cash' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }} font-medium capitalize">
                                    {{ $payment->payment_method === 'cash' ? '💵 Tunai' : '🏦 Transfer' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">
                                Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->status === 'verified')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Verified
                                    </span>
                                @elseif($payment->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                        ⏳ Menunggu
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800"
                                          title="{{ $payment->notes }}">
                                        ✕ Ditolak
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->proof_file)
                                    <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2 py-1 rounded-md font-medium transition">
                                        📎 Lihat Resi
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($payment->status === 'verified')
                                    <a href="{{ route('siswa.payments.receipt', $payment) }}"
                                       class="px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-lg hover:bg-indigo-100 transition inline-flex items-center gap-1">
                                        🧾 Cetak PDF
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum tersedia</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-gray-400">
                                <p class="text-xl mb-1">📭</p>
                                <p>Belum ada riwayat pembayaran yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
@endsection
