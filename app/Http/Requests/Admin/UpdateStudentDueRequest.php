<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentDueRequest extends FormRequest
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
            'amount'      => ['required', 'numeric', 'min:1'],
            'due_date'    => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul tagihan wajib diisi.',
            'category_id.required' => 'Kategori kas wajib dipilih.',
            'amount.required'      => 'Nominal tagihan wajib diisi.',
            'amount.numeric'       => 'Nominal tagihan harus berupa angka.',
            'due_date.required'    => 'Batas waktu (jatuh tempo) wajib diisi.',
        ];
    }
}
