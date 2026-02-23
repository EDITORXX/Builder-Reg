<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuilderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', BuilderFirm::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'default_lock_days' => 'nullable|integer|min:1|max:365',
        ]);
        $validated['slug'] = Str::slug($validated['name']) . '-' . substr(uniqid(), -4);
        $validated['default_lock_days'] = $validated['default_lock_days'] ?? 30;
        $validated['settings'] = [];
        $validated['is_active'] = true;
        $builder = BuilderFirm::create($validated);
        return response()->json(['data' => $builder, 'message' => 'Success'], 201);
    }

    public function show(BuilderFirm $builder): JsonResponse
    {
        $this->authorize('view', $builder);
        return response()->json(['data' => $builder->load('projects'), 'message' => 'Success']);
    }
}
