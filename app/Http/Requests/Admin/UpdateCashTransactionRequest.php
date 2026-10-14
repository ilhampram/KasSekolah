<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCashTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', 'in:in,out'],
            'category_id'      => ['required', 'exists:cash_categories,id'],
            'class_id'         => ['nullable', 'exists:classes,id'],
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'proof_file'       => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'             => 'Tipe transaksi wajib dipilih.',
            'category_id.required'      => 'Kategori kas wajib dipilih.',
            'title.required'            => 'Judul transaksi wajib diisi.',
            'amount.required'           => 'Nominal transaksi wajib diisi.',
            'amount.numeric'            => 'Nominal transaksi harus berupa angka.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'proof_file.mimes'          => 'Bukti transaksi harus berupa file JPG, PNG, atau PDF.',
            'proof_file.max'            => 'Ukuran bukti transaksi maksimal 5MB.',
        ];
    }
}
