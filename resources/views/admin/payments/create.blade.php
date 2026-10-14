@extends('layouts.app')

@section('title', 'Kasir Pembayaran Kas Siswa')
@section('header', 'Entri Kasir Pembayaran Siswa')

@section('content')
    <div class="max-w-3xl" x-data="cashierForm()">
        <div class="mb-6">
            <a href="{{ route('admin.payments.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Riwayat Pembayaran</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <span class="text-3xl">🧾</span>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Kasir Pembayaran Uang Kas</h3>
                    <p class="text-xs text-gray-500">Mencatat pembayaran siswa secara lunas atau bertahap (cicilan).</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Pilih Tagihan Kas --}}
                <div>
                    <label for="cash_bill_id" class="block text-sm font-semibold text-gray-700 mb-1">
                        Pilih Tagihan Kas <span class="text-red-500">*</span>
                    </label>
                    <select id="cash_bill_id" name="cash_bill_id" x-model="selectedBillId" @change="onBillChange()" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('cash_bill_id') border-red-400 @enderror">
                        <option value="">— Pilih Tagihan —</option>
                        @foreach($bills as $bill)
                            <option value="{{ $bill->id }}"
                                    data-class-id="{{ $bill->class_id }}"
                                    data-amount="{{ $bill->amount }}"
                                    {{ (old('cash_bill_id', $selectedBill?->id) == $bill->id) ? 'selected' : '' }}>
                                {{ $bill->title }} — Kelas: {{ $bill->schoolClass->class_name }} (Rp {{ number_format($bill->amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('cash_bill_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Pilih Siswa --}}
                <div>
                    <label for="student_id" class="block text-sm font-semibold text-gray-700 mb-1">
                        Pilih Siswa <span class="text-red-500">*</span>
                    </label>
                    <select id="student_id" name="student_id" x-model="selectedStudentId" @change="fetchStatus()" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('student_id') border-red-400 @enderror">
                        <option value="">— Pilih Siswa —</option>
                        <template x-for="st in filteredStudents" :key="st.id">
                            <option :value="st.id" x-text="st.name + (st.nisn ? ' (NISN: ' + st.nisn + ')' : '')"></option>
                        </template>
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Live Status Card (Alpine.js) --}}
                <div x-show="isLoading" class="p-4 bg-gray-50 rounded-xl text-center text-sm text-gray-500">
                    Memuat status tagihan siswa...
                </div>

                <div x-show="!isLoading && billDetails.bill_amount > 0" x-transition
                     class="p-5 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl border border-indigo-100">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-700">Ringkasan Tagihan Siswa</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                              :class="{
                                  'bg-emerald-100 text-emerald-800': billDetails.status === 'lunas',
                                  'bg-amber-100 text-amber-800': billDetails.status === 'cicilan',
                                  'bg-gray-100 text-gray-600': billDetails.status === 'belum_bayar'
                              }"
                              x-text="billDetails.status === 'lunas' ? '✓ LUNAS' : (billDetails.status === 'cicilan' ? '⚡ CICILAN' : 'BELUM BAYAR')">
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-white/80 p-3 rounded-lg border border-indigo-100/60">
                            <p class="text-[11px] text-gray-500 font-medium">Total Tagihan</p>
                            <p class="text-sm font-bold text-gray-800 mt-0.5" x-text="formatRupiah(billDetails.bill_amount)"></p>
                        </div>
                        <div class="bg-white/80 p-3 rounded-lg border border-indigo-100/60">
                            <p class="text-[11px] text-gray-500 font-medium">Sudah Dibayar</p>
                            <p class="text-sm font-bold text-emerald-600 mt-0.5" x-text="formatRupiah(billDetails.paid)"></p>
                        </div>
                        <div class="bg-white/80 p-3 rounded-lg border border-indigo-100/60">
                            <p class="text-[11px] text-gray-500 font-medium">Sisa Pembayaran</p>
                            <p class="text-sm font-bold text-rose-600 mt-0.5" x-text="formatRupiah(billDetails.remaining)"></p>
                        </div>
                    </div>

                    <div x-show="billDetails.remaining > 0" class="mt-3 flex items-center justify-end gap-2 text-xs">
                        <span class="text-gray-500">Bayar Cepat:</span>
                        <button type="button" @click="amountPaid = billDetails.remaining"
                                class="px-2.5 py-1 bg-indigo-600 text-white rounded font-medium hover:bg-indigo-700 transition">
                            Bayar Lunas Penuh (Rp <span x-text="formatNumber(billDetails.remaining)"></span>)
                        </button>
                    </div>
                </div>

                {{-- Input Pembayaran --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Jumlah Bayar --}}
                    <div>
                        <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-1">
                            Jumlah Pembayaran (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                            <input type="number" id="amount_paid" name="amount_paid" x-model="amountPaid" min="1" step="0.01" required
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-bold text-gray-800 @error('amount_paid') border-red-400 @enderror"
                                   placeholder="0">
                        </div>
                        <p class="mt-1 text-[11px] text-gray-400">Bisa diisi sebagian untuk pembayaran cicilan.</p>
                        @error('amount_paid')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Bayar --}}
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('payment_date') border-red-400 @enderror">
                        @error('payment_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Metode Pembayaran --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition"
                               :class="paymentMethod === 'cash' ? 'border-indigo-600 bg-indigo-50/50' : 'border-gray-200'">
                            <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">💵 Tunai (Cash)</p>
                                <p class="text-xs text-gray-400">Siswa membayar langsung ke kasir/bendahara</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition"
                               :class="paymentMethod === 'transfer' ? 'border-indigo-600 bg-indigo-50/50' : 'border-gray-200'">
                            <input type="radio" name="payment_method" value="transfer" x-model="paymentMethod" class="text-indigo-600 focus:ring-indigo-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">🏦 Transfer Bank / E-Wallet</p>
                                <p class="text-xs text-gray-400">Siswa transfer via rekening kas</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Upload Bukti jika transfer --}}
                <div x-show="paymentMethod === 'transfer'" x-transition>
                    <label for="proof_file" class="block text-sm font-medium text-gray-700 mb-1">
                        Lampirkan Bukti Transfer <span class="text-gray-400 text-xs">(Opsional)</span>
                    </label>
                    <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition border border-gray-300 rounded-lg p-1.5">
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <input type="text" id="notes" name="notes" value="{{ old('notes') }}"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Contoh: Titipan orang tua / Cicilan pertama">
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-emerald-600 text-white text-sm font-bold rounded-lg hover:bg-emerald-700 focus:ring-4 focus:ring-emerald-200 transition flex items-center gap-2">
                        <span>💾</span> Simpan & Cetak Kwitansi
                    </button>
                    <a href="{{ route('admin.payments.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function cashierForm() {
            const classesData = @js($classes);
            const billsData = @js($bills);
            const initialBillId = '{{ old('cash_bill_id', $selectedBill?->id ?? '') }}';
            const initialStudentId = '{{ old('student_id', $selectedStudent?->id ?? '') }}';
            const initialRemaining = {{ old('amount_paid', $studentRemaining ?? 0) }};

            return {
                classes: classesData,
                bills: billsData,
                selectedBillId: initialBillId,
                selectedStudentId: initialStudentId,
                amountPaid: initialRemaining > 0 ? initialRemaining : '',
                paymentMethod: '{{ old('payment_method', 'cash') }}',
                filteredStudents: [],
                isLoading: false,
                billDetails: {
                    bill_amount: 0,
                    paid: 0,
                    remaining: 0,
                    status: 'belum_bayar'
                },

                init() {
                    if (this.selectedBillId) {
                        this.updateStudentsList();
                        if (this.selectedStudentId) {
                            this.fetchStatus();
                        }
                    }
                },

                onBillChange() {
                    this.updateStudentsList();
                    this.selectedStudentId = '';
                    this.billDetails = { bill_amount: 0, paid: 0, remaining: 0, status: 'belum_bayar' };
                    this.amountPaid = '';
                },

                updateStudentsList() {
                    const bill = this.bills.find(b => b.id == this.selectedBillId);
                    if (!bill) {
                        this.filteredStudents = [];
                        return;
                    }
                    const classObj = this.classes.find(c => c.id == bill.class_id);
                    this.filteredStudents = classObj ? (classObj.students || []) : [];
                },

                async fetchStatus() {
                    if (!this.selectedBillId || !this.selectedStudentId) return;

                    this.isLoading = true;
                    try {
                        const url = `{{ route('admin.payments.bill-status') }}?bill_id=${this.selectedBillId}&student_id=${this.selectedStudentId}`;
                        const response = await fetch(url);
                        const data = await response.json();
                        this.billDetails = data;
                        if (!this.amountPaid || this.amountPaid == 0) {
                            this.amountPaid = data.remaining;
                        }
                    } catch (e) {
                        console.error('Failed to fetch status', e);
                    } finally {
                        this.isLoading = false;
                    }
                },

                formatRupiah(num) {
                    return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
                },

                formatNumber(num) {
                    return Number(num || 0).toLocaleString('id-ID');
                }
            };
        }
    </script>
@endsection
