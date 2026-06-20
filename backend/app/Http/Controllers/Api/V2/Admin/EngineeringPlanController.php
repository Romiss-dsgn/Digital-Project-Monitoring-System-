<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EngineeringPlan;
use Illuminate\Support\Facades\Auth;

class EngineeringPlanController extends Controller
{
    /**
     * Store a newly created engineering plan in storage.
     *
     * Accepts fields matching the existing `engineering_plans` table.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'plan_title'  => ['required', 'string', 'max:255'],
            'plan_type'   => ['nullable', 'string', 'max:255'],
            'version'     => ['nullable', 'string', 'max:255'],
            'file_name'   => ['required', 'string', 'max:255'],
            'file_path'   => ['required', 'string', 'max:1000'],
            'file_type'   => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'string', 'max:100'],
            'remarks'     => ['nullable', 'string'],
        ]);

        // Attach uploader information if authenticated
        $user = $request->user();
        if ($user) {
            $validated['uploaded_by'] = $user->id;
            $validated['uploaded_at'] = now();
        }

        // Create the EngineeringPlan using guarded=[] on the model
        $plan = EngineeringPlan::create($validated);

        return response()->json(['data' => $plan], 201);
    }
}
