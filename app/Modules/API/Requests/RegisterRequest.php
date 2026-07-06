<?php

namespace App\Modules\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'admission_number' => ['nullable', 'string', 'max:50', 'unique:users,admission_number'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:users,employee_id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:student,lecturer,staff'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered.',
            'admission_number.unique' => 'This admission number is already registered.',
            'employee_id.unique' => 'This employee ID is already registered.',
            'role.in' => 'Please select a valid role (student, lecturer, or staff).',
        ];
    }
}
