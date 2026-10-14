@extends('layouts.app')

@section('title', 'Edit Tagihan')
@section('header', 'Edit Tagihan')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('admin.dues.show', $bill) }}" class="text-sm text-indigo-600 hover:underline">← Kembali ke Detail Tagihan</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-6">Edit Tagihan: {{ $bill->title }}</h3>

            <form method="POST" action="{{ route('admin.dues.update', $bill) }}" class="space-y-6">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <input type="text" value="{{ $bill->schoolClass->class_name }}" disabled
                           class="w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed">
                    <p class="mt-1 text-xs text-gray-400">Kelas target tidak dapat diubah setelah dibuat.</p>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori Kas <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $bill->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Tagihan <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title', $bill->title) }}" required
                           class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Nominal per Siswa (Rp) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm font-semibold">Rp</span>
                            <input type="number" id="amount" name="amount" value="{{ old('amount', $bill->amount) }}" min="1" step="0.01" required
                                   class="w-full rounded-lg border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-semibold">
                        </div>
                    </div>

                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-1">Jatuh Tempo <span class="text-red-500">*</span></label>
                        <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $bill->due_date->format('Y-m-d')) }}" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>

                <div class="flex gap-3 pt-3 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                        💾 Update Tagihan
                    </button>
                    <a href="{{ route('admin.dues.show', $bill) }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
