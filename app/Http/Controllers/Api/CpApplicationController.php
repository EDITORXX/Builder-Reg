<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CpApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CpApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CpApplication::class);
        $query = CpApplication::with(['channelPartner.user', 'builderFirm']);
        if ($request->user()->builder_firm_id && ! $request->user()->isSuperAdmin()) {
            $query->where('builder_firm_id', $request->user()->builder_firm_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $applications = $query->latest()->paginate($request->input('per_page', 20));
        return response()->json([
            'data' => $applications->items(),
            'meta' => [
                'total' => $applications->total(),
                'page' => $applications->currentPage(),
                'per_page' => $applications->perPage(),
                'last_page' => $applications->lastPage(),
            ],
            'message' => 'Success',
        ]);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $cp = $request->user()->channelPartner;
        if (! $cp) {
            return response()->json(['data' => [], 'message' => 'Success']);
        }
        $applications = CpApplication::with('builderFirm')
            ->where('channel_partner_id', $cp->id)
            ->latest()
            ->get();
        return response()->json(['data' => $applications, 'message' => 'Success']);
    }

    public function apply(Request $request): JsonResponse
    {
        $this->authorize('create', CpApplication::class);
        $validated = $request->validate([
            'builder_firm_id' => 'required|exists:builder_firms,id',
            'documents' => 'nullable|array',
            'documents.*.type' => 'string|max:50',
            'documents.*.path' => 'string|max:500',
        ]);
        $cp = $request->user()->channelPartner;
        if (! $cp) {
            return response()->json(['error' => 'Channel partner profile not found.'], 403);
        }
        $exists = CpApplication::where('channel_partner_id', $cp->id)
            ->where('builder_firm_id', $validated['builder_firm_id'])
            ->first();
        if ($exists) {
            return response()->json(['error' => 'You have already applied to this builder.'], 422);
        }
        $app = CpApplication::create([
            'channel_partner_id' => $cp->id,
            'builder_firm_id' => $validated['builder_firm_id'],
            'status' => CpApplication::STATUS_PENDING,
            'notes' => null,
        ]);
        if (! empty($validated['documents'])) {
            $app->update(['documents' => $validated['documents']]);
        }
        $app->load('builderFirm');
        return response()->json(['data' => $app, 'message' => 'Success'], 201);
    }

    public function approve(CpApplication $cpApplication): JsonResponse
    {
        $this->authorize('approve', $cpApplication);
        $cpApplication->update([
            'status' => CpApplication::STATUS_APPROVED,
            'reviewed_by' => request()->user()->id,
            'reviewed_at' => now(),
            'notes' => null,
        ]);
        return response()->json(['data' => $cpApplication->fresh(['channelPartner.user', 'builderFirm']), 'message' => 'Success']);
    }

    public function reject(Request $request, CpApplication $cpApplication): JsonResponse
    {
        $this->authorize('reject', $cpApplication);
        $validated = $request->validate(['notes' => 'required|string|max:1000']);
        $cpApplication->update([
            'status' => CpApplication::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'notes' => $validated['notes'],
        ]);
        return response()->json(['data' => $cpApplication->fresh(), 'message' => 'Success']);
    }

    public function needsInfo(Request $request, CpApplication $cpApplication): JsonResponse
    {
        $this->authorize('reject', $cpApplication);
        $validated = $request->validate(['notes' => 'required|string|max:1000']);
        $cpApplication->update([
            'status' => CpApplication::STATUS_NEEDS_INFO,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'notes' => $validated['notes'],
        ]);
        return response()->json(['data' => $cpApplication->fresh(), 'message' => 'Success']);
    }
}
