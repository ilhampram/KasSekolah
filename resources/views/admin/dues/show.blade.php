@extends('layouts.app')

@section('title', 'Detail Tagihan: ' . $bill->title)
@section('header', 'Pantau Tagihan Siswa')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.dues.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Daftar Tagihan</a>
            <h2 class="text-xl font-bold text-gray-800 mt-1">{{ $bill->title }}</h2>
            <p class="text-xs text-gray-500">
                Kelas: <span class="font-semibold text-gray-700">{{ $bill->schoolClass->class_name }}</span> |
                Kategori: <span class="font-semibold text-gray-700">{{ $bill->category->name }}</span> |
                Jatuh Tempo: <span class="font-semibold text-gray-700">{{ $bill->due_date->format('d M Y') }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.payments.create', ['bill_id' => $bill->id]) }}"
               class="px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition flex items-center gap-1.5">
                <span>🧾</span> Buka Kasir Pembayaran
            </a>
            <a href="{{ route('admin.dues.edit', $bill) }}"
               class="px-3 py-2 bg-amber-50 text-amber-700 text-xs font-semibold rounded-lg hover:bg-amber-100 transition">
                ✏️ Edit Tagihan
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 font-medium">Nominal / Siswa</p>
            <p class="text-lg font-bold text-gray-800 mt-1">Rp {{ number_format($bill->amount, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 font-medium">Total Terkumpul</p>
            <p class="text-lg font-bold text-emerald-600 mt-1">Rp {{ number_format($totalCollected, 0, ',', '.') }}</p>
            <p class="text-[11px] text-gray-400">Target: Rp {{ number_format($targetTotal, 0, ',', '.') }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-emerald-700 font-medium">Lunas</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">{{ $totalLunas }} <span class="text-xs font-normal text-gray-500">Siswa</span></p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-amber-700 font-medium">Cicilan (Belum Lunas)</p>
            <p class="text-xl font-bold text-amber-600 mt-1">{{ $totalCicilan }} <span class="text-xs font-normal text-gray-500">Siswa</span></p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 col-span-2 sm:col-span-1">
            <p class="text-xs text-rose-700 font-medium">Belum Bayar</p>
            <p class="text-xl font-bold text-rose-600 mt-1">{{ $totalBelum }} <span class="text-xs font-normal text-gray-500">Siswa</span></p>
        </div>
    </div>

    {{-- Progress Bar --}}
    @php
        $percentage = $targetTotal > 0 ? min(100, round(($totalCollected / $targetTotal) * 100, 1)) : 0;
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between text-xs font-semibold mb-1.5">
            <span class="text-gray-700">Progres Pembayaran Kelas {{ $bill->schoolClass->class_name }}</span>
            <span class="text-indigo-600">{{ $percentage }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
            <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
        </div>
    </div>

    {{-- Students Payment Status Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm">Status Pembayaran Siswa Kelas ({{ $students->count() }} Siswa)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">NISN</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dibayar</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Tagihan</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $i => $student)
                        @php
                            $statusData = $studentStatuses[$student->id];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-gray-500">{{ $i + 1 }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-gray-500 font-mono text-xs">{{ $student->nisn ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($statusData['status'] === 'lunas')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✓ Lunas
                                    </span>
                                @elseif($statusData['status'] === 'cicilan')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        ⚡ Cicilan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-800">
                                Rp {{ number_format($statusData['paid'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold {{ $statusData['remaining'] > 0 ? 'text-rose-600' : 'text-gray-400' }}">
                                Rp {{ number_format($statusData['remaining'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($statusData['remaining'] > 0)
                                    <a href="{{ route('admin.payments.create', ['bill_id' => $bill->id, 'student_id' => $student->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition">
                                        <span>💳</span> Bayar Kasir
                                    </a>
                                @else
                                    <span class="text-xs text-emerald-600 font-semibold">Tuntas</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada siswa yang terdaftar di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Riwayat Pembayaran Tagihan Ini --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-bold text-gray-800 text-sm">Riwayat Pembayaran Masuk ({{ $bill->payments->count() }})</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Kwitansi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Dibayar</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Kwitansi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bill->payments as $payment)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-800">{{ $payment->receipt_number ?: '-' }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $payment->student->name }}</td>
                            <td class="px-6 py-4 text-gray-600 text-xs">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 capitalize text-xs text-gray-600">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4 text-right font-bold text-emerald-600">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                @if($payment->status === 'verified')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Verified</span>
                                @elseif($payment->status === 'pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pending</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.payments.receipt', $payment) }}"
                                   class="px-2.5 py-1.5 text-xs bg-indigo-50 text-indigo-700 font-semibold rounded-md hover:bg-indigo-100 transition inline-flex items-center gap-1">
                                    🧾 Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada riwayat pembayaran untuk tagihan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
