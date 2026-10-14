<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentDueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:cash_categories,id'],
            'target_type' => ['required', 'in:single,all'],
            'class_id'    => ['required_if:target_type,single', 'nullable', 'exists:classes,id'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'due_date'    => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'        => 'Judul tagihan wajib diisi.',
            'category_id.required' => 'Kategori kas wajib dipilih.',
            'target_type.required' => 'Target kelas wajib dipilih.',
            'class_id.required_if' => 'Kelas harus dipilih jika target adalah satu kelas.',
            'class_id.exists'      => 'Kelas yang dipilih tidak valid.',
            'amount.required'      => 'Nominal tagihan wajib diisi.',
            'amount.numeric'       => 'Nominal tagihan harus berupa angka.',
            'amount.min'           => 'Nominal minimal Rp 1.',
            'due_date.required'    => 'Batas waktu (jatuh tempo) wajib diisi.',
        ];
    }
}
