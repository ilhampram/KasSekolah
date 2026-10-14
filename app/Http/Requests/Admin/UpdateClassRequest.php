<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_name'       => ['required', 'string', 'max:100'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'homeroom_teacher' => ['required', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'class_name.required'       => 'Nama kelas wajib diisi.',
            'academic_year_id.required' => 'Tahun ajaran wajib dipilih.',
            'academic_year_id.exists'   => 'Tahun ajaran tidak valid.',
            'homeroom_teacher.required' => 'Wali kelas wajib diisi.',
        ];
    }
}
