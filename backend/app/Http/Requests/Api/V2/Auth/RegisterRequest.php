<?php

namespace App\Http\Requests\Api\V2\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'badge_number' => ['required', 'string', 'max:255', 'unique:users,badge_number'],
            'department' => [
                'required',
                'string',
                Rule::in([
                    'contract-documentation',
                    'records-management',
                    'contract-monitoring',
                    'engineering-planning',
                    'engineering-supervision',
                    'engineering-monitoring',
                    'regional-admin',
                ]),
            ],
            'requested_role' => [
                'required',
                'string',
                Rule::in([
                    'administrative-staff',
                    'records-management-personnel',
                    'contract-monitoring-personnel',
                    'engineer-planning',
                    'engineer-supervision',
                    'engineer-monitoring',
                    'system-administrator',
                ]),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
