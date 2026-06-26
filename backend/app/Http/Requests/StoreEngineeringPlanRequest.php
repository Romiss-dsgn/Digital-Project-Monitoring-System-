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
            // Engineering plan documents must be traceable to an active project.
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id')->where(fn ($query) => $query->where('is_archived', false)),
            ],
            'plan_title' => ['required', 'string', 'max:255'],
            'plan_type' => ['required', 'string', Rule::in(EngineeringPlan::PLAN_TYPES)],
            'version' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', Rule::in(EngineeringPlan::STATUSES)],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:' . self::MAX_FILE_SIZE_KB],
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
