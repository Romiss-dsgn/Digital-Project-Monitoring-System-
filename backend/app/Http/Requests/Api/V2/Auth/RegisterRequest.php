<?php

namespace App\Http\Requests\Api\V2\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => $this->input('full_name', $this->input('name')),
            'office_unit' => $this->input('office_unit', $this->input('department')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                Rule::unique('access_requests', 'email')->where('status', 'Pending'),
            ],
            'badge_number' => [
                'required',
                'string',
                'max:255',
                'unique:users,badge_number',
                Rule::unique('access_requests', 'badge_number')->where('status', 'Pending'),
            ],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'office_unit' => ['required', 'string', 'max:255'],
            'requested_role_id' => ['nullable', 'integer', 'exists:roles,id', 'required_without:requested_role'],
            'requested_role' => ['nullable', 'string', 'max:255', 'required_without:requested_role_id'],
            'reason' => ['nullable', 'string', 'max:2000'],

            // Accepted for backward compatibility with the current request-access form.
            // Password setup should happen after an administrator approves the account.
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }
}
