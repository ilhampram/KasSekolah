<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year'      => ['required', 'string', 'max:20', 'regex:/^\d{4}\/\d{4}$/'],
            'semester'  => ['required', 'in:Ganjil,Genap'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'year.required' => 'Format tahun ajaran wajib diisi.',
            'year.regex'    => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).',
            'semester.in'   => 'Pilihan semester harus Ganjil atau Genap.',
        ];
    }
}
