@extends('layouts.app')

@section('title', 'Detail Transaksi Kas')
@section('header', 'Detail Transaksi Kas')

@section('content')
    <div class="max-w-3xl">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Buku Kas</a>
            @if(! $transaction->payment_id)
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.transactions.edit', $transaction) }}"
                       class="px-3 py-1.5 text-xs bg-amber-50 text-amber-700 font-medium rounded-md hover:bg-amber-100 transition">
                        ✏️ Edit
                    </a>
                    <form method="POST" action="{{ route('admin.transactions.destroy', $transaction) }}"
                          onsubmit="return confirm('Hapus transaksi ini?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="px-3 py-1.5 text-xs bg-red-50 text-red-600 font-medium rounded-md hover:bg-red-100 transition">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            {{-- Top Banner --}}
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 {{ $transaction->type === 'in' ? 'bg-emerald-50/50' : 'bg-rose-50/50' }}">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-sm font-bold text-gray-800">{{ $transaction->transaction_number }}</span>
                        @if($transaction->type === 'in')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                ＋ Pemasukan
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                － Pengeluaran
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mt-1">{{ $transaction->title }}</h2>
                </div>

                <div class="text-left sm:text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Nominal Transaksi</p>
                    <p class="text-2xl font-bold {{ $transaction->type === 'in' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $transaction->type === 'in' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Detail Info Grid --}}
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Tanggal Transaksi</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $transaction->transaction_date->translatedFormat('d F Y') }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Kategori Kas</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $transaction->category->name }}</p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Lingkup Kas / Kelas</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">
                        {{ $transaction->schoolClass ? $transaction->schoolClass->class_name . ' (' . $transaction->schoolClass->academic_year . ')' : 'Kas Umum Sekolah (Semua Kelas)' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Dicatat Oleh</p>
                    <p class="text-sm font-semibold text-gray-800 mt-1">{{ $transaction->creator->name ?? 'Sistem' }}</p>
                </div>

                <div class="sm:col-span-2">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Catatan</p>
                    <p class="text-sm text-gray-700 mt-1 bg-gray-50 p-3 rounded-lg border border-gray-100">
                        {{ $transaction->notes ?: 'Tidak ada catatan tambahan.' }}
                    </p>
                </div>

                {{-- Linked Payment info if any --}}
                @if($transaction->payment)
                    <div class="sm:col-span-2 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                        <p class="text-xs text-indigo-700 font-bold uppercase tracking-wider mb-2">Terhubung ke Pembayaran Siswa</p>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-sm">
                            <div>
                                <p class="font-semibold text-indigo-950">{{ $transaction->payment->student->name }} (NISN: {{ $transaction->payment->student->nisn ?? '-' }})</p>
                                <p class="text-xs text-indigo-700 mt-0.5">Tagihan: {{ $transaction->payment->cashBill->title }}</p>
                            </div>
                            <a href="{{ route('admin.payments.receipt', $transaction->payment) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">
                                🧾 Buka Kwitansi Digital
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Proof File --}}
                @if($transaction->proof_file)
                    <div class="sm:col-span-2 border-t border-gray-100 pt-4">
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wider mb-3">Lampiran Bukti Transaksi</p>
                        @php
                            $ext = strtolower(pathinfo($transaction->proof_file, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <div class="border border-gray-200 rounded-lg p-2 max-w-md bg-gray-50">
                                <img src="{{ asset('storage/' . $transaction->proof_file) }}" alt="Bukti Transaksi" class="w-full rounded-md shadow-sm max-h-96 object-contain">
                            </div>
                        @else
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-2xl">📄</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800 truncate">{{ basename($transaction->proof_file) }}</p>
                                    <p class="text-xs text-gray-400">Dokumen PDF</p>
                                </div>
                                <a href="{{ asset('storage/' . $transaction->proof_file) }}" target="_blank"
                                   class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">
                                    Unduh / Lihat
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
