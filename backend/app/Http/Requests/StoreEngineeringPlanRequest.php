<?php

namespace App\Http\Requests;

use App\Models\EngineeringPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEngineeringPlanRequest extends FormRequest
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'dwg', 'png', 'docx'];

    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/png',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/acad',
        'application/autocad_dwg',
        'application/dwg',
        'application/x-autocad',
        'image/vnd.dwg',
        'image/x-dwg',
    ];

    private const MAX_FILE_SIZE_KB = 25_600;

    public function authorize(): bool
    {
        return true;
    }

 public function rules(): array
{
    return [
        'project_id' => ['nullable', 'integer', 'exists:projects,id'],
        'plan_title' => ['nullable', 'string', 'max:255'],
        'plan_type'  => ['nullable', 'string'],
        'version'    => ['nullable', 'string', 'max:50'],
        'status'     => ['nullable', 'string'],
        'remarks'    => ['nullable', 'string', 'max:2000'],
        'file'       => ['nullable', 'file', 'max:25600'],
    ];
}
    public function messages(): array
    {
        return [
            'project_id.required' => 'Please select a project.',
            'project_id.exists'   => 'The selected project does not exist.',
            'plan_title.required' => 'Plan title is required.',
            'plan_type.in'        => 'Invalid plan type selected.',
            'status.in'           => 'Invalid review status selected.',
            'file.required'       => 'Please attach a plan file.',
            'file.max'            => 'The file must not exceed 25 MB.',
        ];
    }
}