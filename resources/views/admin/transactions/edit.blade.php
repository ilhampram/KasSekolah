@extends('layouts.app')

@section('title', 'Edit Transaksi Kas')
@section('header', 'Edit Transaksi Kas')

@section('content')
    <div class="max-w-3xl" x-data="{
        type: '{{ old('type', $transaction->type) }}',
        incomeCategories: @js($incomeCategories),
        expenseCategories: @js($expenseCategories),
        get currentCategories() {
            return this.type === 'in' ? this.incomeCategories : this.expenseCategories;
        }
    }">
        <div class="mb-6">
            <a href="{{ route('admin.transactions.show', $transaction) }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Detail Transaksi</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Edit Transaksi: {{ $transaction->transaction_number }}</h3>

            <form method="POST" action="{{ route('admin.transactions.update', $transaction) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf @method('PUT')

                {{-- Tipe Transaksi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Transaksi</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition text-sm font-semibold"
                               :class="type === 'in' ? 'border-emerald-600 bg-emerald-50 text-emerald-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="type" value="in" x-model="type" class="sr-only">
                            <span class="text-lg">💰</span>
                            <span>Pemasukan Kas (In)</span>
                        </label>

                        <label class="relative flex items-center justify-center gap-2 p-3 border-2 rounded-xl cursor-pointer transition text-sm font-semibold"
                               :class="type === 'out' ? 'border-rose-600 bg-rose-50 text-rose-800' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                            <input type="radio" name="type" value="out" x-model="type" class="sr-only">
                            <span class="text-lg">💸</span>
                            <span>Pengeluaran Kas (Out)</span>
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Kategori Kas --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Kategori Kas <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <template x-for="cat in currentCategories" :key="cat.id">
                                <option :value="cat.id" :selected="cat.id == {{ old('category_id', $transaction->category_id) }}" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Kelas (Opsional) --}}
                    <div>
                        <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Kelas Terkait <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                        </label>
                        <select id="class_id" name="class_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">— Kas Umum Sekolah (Tanpa Kelas) —</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $transaction->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->class_name }} ({{ $class->academic_year }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Judul Transaksi --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Judul Transaksi <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $transaction->title) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Nominal --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Nominal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', $transaction->amount) }}" min="1" step="0.01" required
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-semibold">
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>

                {{-- Upload Bukti Transaksi --}}
                <div>
                    <label for="proof_file" class="block text-sm font-medium text-gray-700 mb-1">
                        Ganti Bukti Transaksi <span class="text-gray-400 text-xs font-normal">(Kosongkan jika tidak diubah)</span>
                    </label>
                    @if($transaction->proof_file)
                        <div class="mb-2 text-xs text-gray-500 flex items-center gap-2">
                            <span>File saat ini:</span>
                            <a href="{{ asset('storage/' . $transaction->proof_file) }}" target="_blank" class="text-indigo-600 underline">Lihat Lampiran</a>
                        </div>
                    @endif
                    <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition border border-gray-300 rounded-lg p-1.5">
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan Tambahan
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('notes', $transaction->notes) }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Update Transaksi
                    </button>
                    <a href="{{ route('admin.transactions.show', $transaction) }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
