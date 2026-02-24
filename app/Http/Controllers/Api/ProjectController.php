<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::with('builderFirm');
        if ($request->user()->isSuperAdmin() && $request->filled('builder_id')) {
            $query->where('builder_firm_id', $request->builder_id);
        } elseif ($request->user()->builder_firm_id) {
            $query->where('builder_firm_id', $request->user()->builder_firm_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $projects = $query->orderBy('name')->paginate($request->input('per_page', 20));
        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'total' => $projects->total(),
                'page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'last_page' => $projects->lastPage(),
            ],
            'message' => 'Success',
        ]);
    }

    public function store(Request $request, BuilderFirm $builder): JsonResponse
    {
        $this->authorize('create', Project::class);
        if (! $request->user()->isSuperAdmin() && (int) $request->user()->builder_firm_id !== (int) $builder->id) {
            return response()->json(['error' => 'Forbidden.'], 403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'lock_days_override' => 'nullable|integer|min:1|max:365',
        ]);
        $builder->load('plan');
        if ($builder->projects()->count() >= $builder->getMaxProjects()) {
            return response()->json([
                'error' => 'Project limit reached for this tenant. Plan allows ' . $builder->getMaxProjects() . ' project(s).',
            ], 422);
        }
        $validated['builder_firm_id'] = $builder->id;
        $validated['status'] = 'active';
        $project = Project::create($validated);
        return response()->json(['data' => $project, 'message' => 'Success'], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'lock_days_override' => 'nullable|integer|min:1|max:365',
            'status' => 'sometimes|in:active,inactive',
        ]);
        $project->update($validated);
        return response()->json(['data' => $project->fresh(), 'message' => 'Success']);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json(['message' => 'Success']);
    }
}
