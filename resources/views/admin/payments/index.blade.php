@extends('layouts.app')

@section('title', 'Verifikasi & Riwayat Pembayaran')
@section('header', 'Verifikasi & Riwayat Pembayaran')

@section('content')
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Verifikasi & Riwayat Pembayaran</h2>
            <p class="text-sm text-gray-500">Pencatatan pembayaran kas siswa secara tunai/transfer & verifikasi bukti transfer.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dues.index') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 text-indigo-700 border border-indigo-200 text-sm font-semibold rounded-lg hover:bg-indigo-100 transition">
                <span>📋</span> Tagihan Kelas
            </a>
            <a href="{{ route('admin.payments.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition shadow-sm">
                <span>💳</span> + Entri Kasir Baru
            </a>
        </div>
    </div>

    {{-- Quick Status Filter Pills --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <a href="{{ route('admin.payments.index') }}"
           class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ !request('status') ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Semua Riwayat
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'pending']) }}"
           class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-semibold transition {{ request('status') === 'pending' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-amber-700 border border-amber-200 hover:bg-amber-50' }}">
            <span>⏳</span> Perlu Verifikasi
            @if($pendingCount > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black {{ request('status') === 'pending' ? 'bg-white text-amber-700' : 'bg-amber-100 text-amber-800' }}">
                    {{ $pendingCount }}
                </span>
            @endif
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'verified']) }}"
           class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ request('status') === 'verified' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50' }}">
            ✓ Terverifikasi
        </a>
        <a href="{{ route('admin.payments.index', ['status' => 'rejected']) }}"
           class="px-4 py-1.5 rounded-full text-xs font-semibold transition {{ request('status') === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-700 border border-rose-200 hover:bg-rose-50' }}">
            ✕ Ditolak
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-2xl">💵</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Pembayaran Terverifikasi</p>
                    <p class="text-xl font-bold text-emerald-600">Rp {{ number_format($totalVerifiedAmount, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center text-2xl">⏳</div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Menunggu Verifikasi (Transfer)</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $pendingCount }} <span class="text-xs font-normal text-gray-500">Transaksi</span></p>
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
                       placeholder="Cari No. Kwitansi, nama siswa, NISN...">
            </div>

            <div>
                <select name="status"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified (Lunas/Cicil)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Menunggu)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                </select>
            </div>

            <div>
                <select name="class_id"
                        class="w-full rounded-lg border-gray-300 text-sm px-4 py-2 border focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                            {{ $class->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    🔍 Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'class_id', 'start_date', 'end_date']))
                    <a href="{{ route('admin.payments.index') }}"
                       class="px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Payments Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Kwitansi & Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa & Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tagihan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Nominal</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-mono text-xs font-bold text-gray-800">{{ $payment->receipt_number ?: '-' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $payment->payment_date->format('d M Y') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800">{{ $payment->student->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    NISN: {{ $payment->student->nisn ?? '-' }} |
                                    <span class="text-indigo-600 font-medium">{{ $payment->cashBill->schoolClass->class_name }}</span>
                                </p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $payment->cashBill->title }}</p>
                                <p class="text-xs text-gray-400">Total Tagihan: Rp {{ number_format($payment->cashBill->amount, 0, ',', '.') }}</p>
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
                                        ⏳ Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                        ✕ Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($payment->status === 'verified')
                                        <a href="{{ route('admin.payments.receipt', $payment) }}"
                                           class="px-2.5 py-1.5 text-xs bg-indigo-50 text-indigo-700 font-semibold rounded-md hover:bg-indigo-100 transition inline-flex items-center gap-1">
                                            🧾 Kwitansi
                                        </a>
                                    @elseif($payment->status === 'pending')
                                        @if($payment->proof_file)
                                            <a href="{{ asset('storage/' . $payment->proof_file) }}" target="_blank"
                                               class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition"
                                               title="Lihat Bukti Transfer">
                                                📎
                                            </a>
                                        @endif
                                        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2.5 py-1 text-xs bg-emerald-600 text-white font-medium rounded-md hover:bg-emerald-700 transition"
                                                    onclick="return confirm('Verifikasi pembayaran ini? Akan otomatis tercatat ke buku kas.')">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.payments.reject', $payment) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="px-2 py-1 text-xs bg-rose-100 text-rose-700 font-medium rounded-md hover:bg-rose-200 transition"
                                                    onclick="return confirm('Tolak pembayaran ini?')">
                                                ✕
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
                                <p class="font-medium">Belum ada riwayat pembayaran siswa.</p>
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
