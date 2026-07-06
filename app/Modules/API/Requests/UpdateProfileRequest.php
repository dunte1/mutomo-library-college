<?php

namespace App\Modules\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'notification_preferences' => ['sometimes', 'array'],
            'notification_preferences.in_app' => ['boolean'],
            'notification_preferences.email' => ['boolean'],
            'notification_preferences.push' => ['boolean'],
            'notification_preferences.sms' => ['boolean'],
        ];
    }
}
