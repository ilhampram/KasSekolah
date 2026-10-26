@extends('layouts.app')

@section('title', 'Catat Transaksi Kas')
@section('header', 'Catat Transaksi Kas')

@section('content')
    <div class="max-w-3xl" x-data="{
        type: '{{ old('type', request('type', 'in')) }}',
        incomeCategories: @js($incomeCategories),
        expenseCategories: @js($expenseCategories),
        get currentCategories() {
            return this.type === 'in' ? this.incomeCategories : this.expenseCategories;
        }
    }">
        <div class="mb-6">
            <a href="{{ route('admin.transactions.index') }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Buku Kas</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800" x-text="type === 'out' ? 'Form Pencatatan Pengeluaran Kas' : 'Form Pencatatan Pemasukan Kas'">
                    Form Pencatatan Transaksi Kas
                </h3>
                @if(isset($currentBalance))
                    <div class="px-3 py-1.5 bg-gray-100 rounded-lg text-xs font-semibold text-gray-700">
                        Saldo Kas: <strong class="{{ $currentBalance >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">Rp {{ number_format($currentBalance, 0, ',', '.') }}</strong>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.transactions.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Tipe Transaksi (Tabs / Radios) --}}
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
                    @error('type')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Kategori Kas --}}
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Kategori Kas <span class="text-red-500">*</span>
                        </label>
                        <select id="category_id" name="category_id" required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('category_id') border-red-400 @enderror">
                            <option value="">— Pilih Kategori —</option>
                            <template x-for="cat in currentCategories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
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
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
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
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('title') border-red-400 @enderror"
                           placeholder="Contoh: Pembelian spidol & penghapus papan tulis">
                    @error('title')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Nominal --}}
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Nominal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount') }}" min="1" step="0.01" required
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-semibold @error('amount') border-red-400 @enderror"
                                   placeholder="0">
                        </div>
                        @error('amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none @error('transaction_date') border-red-400 @enderror">
                        @error('transaction_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Upload Bukti Transaksi --}}
                <div>
                    <label for="proof_file" class="block text-sm font-medium text-gray-700 mb-1">
                        Bukti Transaksi (Nota / Struk / Resi) <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                    </label>
                    <input type="file" id="proof_file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition border border-gray-300 rounded-lg p-1.5">
                    <p class="mt-1 text-xs text-gray-400">Format didukung: JPG, PNG, PDF (Maks. 5MB)</p>
                    @error('proof_file')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan Tambahan <span class="text-gray-400 text-xs font-normal">(Opsional)</span>
                    </label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Keterangan tambahan transaksi...">{{ old('notes') }}</textarea>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Simpan Transaksi
                    </button>
                    <a href="{{ route('admin.transactions.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
