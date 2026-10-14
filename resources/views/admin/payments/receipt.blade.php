@extends('layouts.app')

@section('title', 'Kwitansi Pembayaran: ' . ($payment->receipt_number ?: 'KWT'))
@section('header', 'Kwitansi Digital')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Navigation & Actions (Hidden on Print) --}}
        <div class="no-print mb-6 flex items-center justify-between gap-3">
            @if(auth()->user()->role === 'siswa')
                <a href="{{ route('siswa.payments.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke Riwayat Pembayaran
                </a>
            @else
                <a href="{{ route('admin.payments.index') }}" class="text-sm text-indigo-600 hover:underline">
                    ← Kembali ke Riwayat Pembayaran
                </a>
            @endif

            <div class="flex items-center gap-2">
                @if(auth()->user()->role !== 'siswa')
                    <a href="{{ route('admin.payments.create') }}"
                       class="px-3 py-2 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200 transition">
                        ＋ Entri Kasir Baru
                    </a>
                @endif
                <button onclick="window.print()"
                        class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition flex items-center gap-1.5 shadow-sm">
                    <span>🖨️</span> Cetak / Simpan PDF
                </button>
            </div>
        </div>

        {{-- Kwitansi Card --}}
        <div class="print-area bg-white rounded-2xl shadow-md border-2 border-indigo-200 overflow-hidden relative">
            {{-- Watermark LUNAS / CICILAN --}}
            <div class="absolute right-8 top-28 pointer-events-none opacity-10 select-none">
                <span class="text-7xl font-black text-indigo-900 tracking-widest uppercase">
                    {{ $statusInfo['status'] === 'lunas' ? 'LUNAS' : 'CICILAN' }}
                </span>
            </div>

            {{-- Header Kop Kwitansi --}}
            <div class="bg-gradient-to-r from-indigo-900 via-indigo-800 to-indigo-950 text-white px-8 py-6 border-b-4 border-amber-400">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-4xl">💰</span>
                        <div>
                            <h1 class="text-xl font-bold tracking-tight">SISTEM KAS SEKOLAH</h1>
                            <p class="text-xs text-indigo-200">Kwitansi Tanda Bukti Pembayaran Resmi</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="inline-block px-3 py-1 bg-amber-400 text-indigo-950 font-black text-xs rounded-full uppercase tracking-wider">
                            Kwitansi Kas
                        </span>
                        <p class="font-mono text-xs text-indigo-200 mt-1">No: {{ $payment->receipt_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Body Kwitansi --}}
            <div class="p-8 space-y-5 text-sm text-gray-700">
                {{-- Metadata Bar --}}
                <div class="flex items-center justify-between text-xs pb-3 border-b border-gray-100">
                    <span class="text-gray-500">Tanggal: <strong class="text-gray-800">{{ $payment->payment_date->translatedFormat('d F Y') }}</strong></span>
                    <span class="text-gray-500">Metode: <strong class="text-gray-800 uppercase">{{ $payment->payment_method }}</strong></span>
                </div>

                {{-- Fields Grid --}}
                <div class="space-y-4">
                    <div class="flex items-start">
                        <span class="w-40 font-medium text-gray-500 flex-shrink-0">Telah Terima Dari</span>
                        <div class="flex-1 font-bold text-gray-900 text-base">
                            {{ $payment->student->name }}
                            <span class="text-xs font-normal text-gray-500 block font-mono">NISN: {{ $payment->student->nisn ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <span class="w-40 font-medium text-gray-500 flex-shrink-0">Kelas</span>
                        <div class="flex-1 font-semibold text-gray-800">
                            {{ $bill->schoolClass->class_name }}
                            <span class="text-xs text-gray-500 font-normal">({{ $bill->schoolClass->academic_year }})</span>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <span class="w-40 font-medium text-gray-500 flex-shrink-0">Untuk Pembayaran</span>
                        <div class="flex-1 font-semibold text-gray-800">
                            {{ $bill->title }}
                            <span class="text-xs text-gray-500 block font-normal">Kategori: {{ $bill->category->name }}</span>
                        </div>
                    </div>

                    {{-- Jumlah & Terbilang Box --}}
                    <div class="bg-indigo-50/70 border border-indigo-100 rounded-xl p-4 my-2">
                        <div class="flex items-baseline justify-between mb-2">
                            <span class="text-xs font-bold uppercase text-indigo-900 tracking-wider">Jumlah Pembayaran</span>
                            <span class="text-2xl font-black text-indigo-700">
                                Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="pt-2 border-t border-indigo-200/60">
                            <span class="text-xs text-gray-500 block">Terbilang:</span>
                            <p class="text-sm font-semibold text-indigo-900 italic mt-0.5">
                                # {{ $terbilang }} #
                            </p>
                        </div>
                    </div>

                    {{-- Status Tagihan Siswa Saat Ini --}}
                    <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200 text-xs flex items-center justify-between">
                        <div>
                            <span class="text-gray-500">Status Tagihan Siswa:</span>
                            <span class="font-bold ml-1 {{ $statusInfo['status'] === 'lunas' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $statusInfo['status'] === 'lunas' ? '✓ SUDAH LUNAS' : '⚡ CICILAN (Belum Lunas)' }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-gray-500">Sisa Tagihan:</span>
                            <strong class="text-gray-900 ml-1">Rp {{ number_format($statusInfo['remaining'], 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if($payment->notes)
                        <div class="text-xs text-gray-500 italic">
                            Catatan: {{ $payment->notes }}
                        </div>
                    @endif
                </div>

                {{-- Tanda Tangan --}}
                <div class="pt-6 border-t border-gray-200 flex items-center justify-between text-xs">
                    <div>
                        <p class="text-gray-400">Dicetak melalui Sistem Kas Sekolah</p>
                        <p class="font-mono text-[10px] text-gray-400">{{ now()->format('d/m/Y H:i:s') }}</p>
                    </div>

                    <div class="text-center w-48">
                        <p class="text-gray-500 mb-12">Petugas Kasir / Bendahara,</p>
                        <p class="font-bold text-gray-900 border-b border-gray-300 pb-1">
                            {{ $payment->verifier->name ?? auth()->user()->name }}
                        </p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Tanda Tangan & Cap Sah</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Print Stylesheet --}}
    <style>
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            #sidebar, header, footer, .no-print {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
            }
            .print-area {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
@endsection
