<?php

namespace App\Http\Controllers\Api\V2\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Return a lightweight list of projects for dropdowns.
     */
    public function index(): JsonResponse
    {
        $projects = Project::select('id', 'project_code', 'project_name')
            ->where('is_archived', false)
            ->orderBy('project_name')
            ->get();

        return response()->json(['data' => $projects]);
    }
}