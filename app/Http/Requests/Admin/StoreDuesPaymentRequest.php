<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDuesPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cash_bill_id'   => ['required', 'exists:cash_bills,id'],
            'student_id'     => ['required', 'exists:users,id'],
            'amount_paid'    => ['required', 'numeric', 'min:1'],
            'payment_date'   => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,transfer'],
            'proof_file'     => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'cash_bill_id.required'   => 'Tagihan wajib dipilih.',
            'cash_bill_id.exists'     => 'Tagihan tidak valid.',
            'student_id.required'     => 'Siswa wajib dipilih.',
            'student_id.exists'       => 'Siswa tidak valid.',
            'amount_paid.required'    => 'Nominal pembayaran wajib diisi.',
            'amount_paid.numeric'     => 'Nominal pembayaran harus berupa angka.',
            'amount_paid.min'         => 'Nominal pembayaran minimal Rp 1.',
            'payment_date.required'   => 'Tanggal pembayaran wajib diisi.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'proof_file.mimes'        => 'Bukti pembayaran harus berupa gambar (JPG, PNG) atau PDF.',
            'proof_file.max'          => 'Ukuran bukti pembayaran maksimal 5MB.',
        ];
    }
}
