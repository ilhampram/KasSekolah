<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($studentId)],
            'nisn'     => ['required', 'string', 'max:20', Rule::unique('users', 'nisn')->ignore($studentId)],
            'class_id' => ['required', 'exists:classes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Nama siswa wajib diisi.',
            'email.required'    => 'Email wajib diisi.',
            'email.unique'      => 'Email sudah terdaftar.',
            'nisn.required'     => 'NISN wajib diisi.',
            'nisn.unique'       => 'NISN sudah terdaftar.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists'   => 'Kelas tidak valid.',
        ];
    }
}
