@extends('layouts.app')

@section('title', 'Upload Bukti Transfer Pembayaran')
@section('header', 'Upload Bukti Pembayaran Kas')

@section('content')
    <div class="max-w-2xl mx-auto" x-data="studentUploadForm()">
        <div class="mb-6">
            <a href="{{ route('siswa.payments.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← Kembali ke Riwayat Pembayaran
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <span class="text-3xl">📤</span>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Kirim Bukti Transfer Pembayaran</h3>
                    <p class="text-xs text-gray-500">Unggah foto atau resi transfer bank / e-wallet untuk dikonfirmasi oleh bendahara.</p>
                </div>
            </div>

            @if(isset($pendingBills) && count($pendingBills) > 0)
                <div class="mb-5 p-4 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                    <p class="font-bold flex items-center gap-1.5 mb-1 text-amber-900">
                        <span>⏳</span> Ada Pengajuan Pembayaran Yang Sedang Diproses:
                    </p>
                    <ul class="list-disc list-inside space-y-0.5 text-amber-700">
                        @foreach($pendingBills as $pb)
                            <li>Tagihan <strong>{{ $pb['bill']->title }}</strong> (Rp {{ number_format($pb['pending'], 0, ',', '.') }}) sedang menunggu persetujuan bendahara.</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(count($activeBills) === 0)
                <div class="p-6 bg-emerald-50 rounded-xl border border-emerald-200 text-center text-emerald-800">
                    <p class="text-3xl mb-2">🎉</p>
                    <h4 class="font-bold text-base">Alhamdulillah, Seluruh Tagihan Kamu Lunas!</h4>
                    <p class="text-xs text-emerald-600 mt-1">Saat ini tidak ada tunggakan kas kelas yang perlu kamu bayar.</p>
                    <a href="{{ route('siswa.dashboard') }}" class="inline-block mt-4 px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 transition">
                        Kembali ke Dashboard
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('siswa.payments.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Pilih Tagihan --}}
                    <div>
                        <label for="cash_bill_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Pilih Tagihan Kas Yang Ingin Dibayar <span class="text-red-500">*</span>
                        </label>
                        <select id="cash_bill_id" name="cash_bill_id" x-model="selectedBillId" @change="onBillChange()" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('cash_bill_id') border-red-400 @enderror">
                            <option value="">— Pilih Tagihan —</option>
                            @foreach($activeBills as $item)
                                <option value="{{ $item['bill']->id }}"
                                        data-amount="{{ $item['bill']->amount }}"
                                        data-remaining="{{ $item['remaining'] }}"
                                        {{ (old('cash_bill_id', $selectedBillId) == $item['bill']->id) ? 'selected' : '' }}>
                                    {{ $item['bill']->title }} (Sisa: Rp {{ number_format($item['remaining'], 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        @error('cash_bill_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info Box Sisa Tagihan --}}
                    <div x-show="currentRemaining > 0" x-transition
                         class="p-4 bg-indigo-50/80 rounded-xl border border-indigo-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-gray-500 block">Sisa Tagihan Kamu:</span>
                            <span class="text-lg font-black text-indigo-900 mt-0.5 block" x-text="formatRupiah(currentRemaining)"></span>
                        </div>
                        <button type="button" @click="amountPaid = currentRemaining"
                                class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition">
                            Bayar Lunas Penuh
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Jumlah Bayar --}}
                        <div>
                            <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-1">
                                Jumlah Yang Kamu Transfer (Rp) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                                <input type="number" id="amount_paid" name="amount_paid" x-model="amountPaid" min="1" step="0.01" required
                                       class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-gray-800 @error('amount_paid') border-red-400 @enderror"
                                       placeholder="0">
                            </div>
                            <p class="mt-1 text-[11px] text-gray-400">Bisa bayar penuh atau sebagian (cicilan).</p>
                            @error('amount_paid')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Transfer --}}
                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Transfer <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                                   class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('payment_date') border-red-400 @enderror">
                            @error('payment_date')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Upload Bukti Resi Transfer --}}
                    <div>
                        <label for="proof_file" class="block text-sm font-medium text-gray-700 mb-1">
                            Foto Bukti Transfer / Resi Struk <span class="text-red-500">*</span>
                        </label>
                        <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" required
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition border border-gray-300 rounded-lg p-1.5">
                        <p class="mt-1 text-xs text-gray-400">Pastikan nominal, tanggal, dan rekening tujuan terlihat jelas (Maks. 5MB).</p>
                        @error('proof_file')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Tambahan <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                        </label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                               class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Contoh: Transfer via BCA a.n Ibu Budi">
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3 pt-3 border-t border-gray-100">
                        <button type="submit"
                                class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition flex items-center gap-2">
                            <span>📤</span> Kirim Bukti Pembayaran
                        </button>
                        <a href="{{ route('siswa.payments.index') }}"
                           class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                            Batal
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <script>
        function studentUploadForm() {
            const activeBillsData = @js($activeBills);
            const initialBillId = '{{ old('cash_bill_id', $selectedBillId ?? '') }}';

            return {
                activeBills: activeBillsData,
                selectedBillId: initialBillId,
                amountPaid: '',
                currentRemaining: 0,

                init() {
                    if (this.selectedBillId) {
                        this.onBillChange();
                    }
                },

                onBillChange() {
                    const item = this.activeBills.find(b => b.bill.id == this.selectedBillId);
                    if (item) {
                        this.currentRemaining = item.remaining;
                        this.amountPaid = item.remaining;
                    } else {
                        this.currentRemaining = 0;
                        this.amountPaid = '';
                    }
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
@endsection
