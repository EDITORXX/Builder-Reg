<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadLock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function leads(Request $request): JsonResponse
    {
        $query = Lead::with(['project', 'customer', 'channelPartner']);
        $user = $request->user();
        if ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $byProject = (clone $query)->selectRaw('project_id, count(*) as total')->groupBy('project_id')->get();
        $byStatus = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->get();
        return response()->json([
            'data' => [
                'by_project' => $byProject,
                'by_status' => $byStatus,
            ],
            'message' => 'Success',
        ]);
    }

    public function locks(Request $request): JsonResponse
    {
        $query = LeadLock::with('project')->active();
        $user = $request->user();
        if ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        $locks = $query->orderBy('end_at')->get();
        return response()->json(['data' => $locks, 'message' => 'Success']);
    }

    public function cpPerformance(Request $request): JsonResponse
    {
        $query = Lead::with('channelPartner');
        $user = $request->user();
        if ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        $perCp = (clone $query)
            ->selectRaw('channel_partner_id, count(*) as lead_count')
            ->groupBy('channel_partner_id')
            ->get();
        $bookedPerCp = (clone $query)->where('status', Lead::STATUS_BOOKED)
            ->selectRaw('channel_partner_id, count(*) as booked_count')
            ->groupBy('channel_partner_id')
            ->get()
            ->keyBy('channel_partner_id');
        foreach ($perCp as $row) {
            $row->booked_count = $bookedPerCp->get($row->channel_partner_id)?->booked_count ?? 0;
        }
        return response()->json(['data' => $perCp, 'message' => 'Success']);
    }

    public function conversion(Request $request): JsonResponse
    {
        $query = Lead::with('project');
        $user = $request->user();
        if ($user->builder_firm_id && ! $user->isSuperAdmin()) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $user->builder_firm_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        $visitDone = (clone $query)->where('status', Lead::STATUS_VISIT_DONE)->count();
        $booked = (clone $query)->where('status', Lead::STATUS_BOOKED)->count();
        $rate = $visitDone > 0 ? round($booked / $visitDone * 100, 2) : 0;
        return response()->json([
            'data' => [
                'visit_done_count' => $visitDone,
                'booked_count' => $booked,
                'conversion_rate_percent' => $rate,
            ],
            'message' => 'Success',
        ]);
    }
}
