<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function leads(Request $request): JsonResponse
    {
        $user = $request->user();
        $builderFirmId = $user->isSuperAdmin() ? null : $user->builder_firm_id;
        $filters = $request->only(['date_from', 'date_to', 'project_id', 'status']);
        $data = $this->reportService->leadsReport($builderFirmId, $filters);

        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ]);
    }

    public function locks(Request $request): JsonResponse
    {
        $user = $request->user();
        $builderFirmId = $user->isSuperAdmin() ? null : $user->builder_firm_id;
        $filters = $request->only(['project_id']);
        $data = $this->reportService->locksReport($builderFirmId, $filters);

        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ]);
    }

    public function cpPerformance(Request $request): JsonResponse
    {
        $user = $request->user();
        $builderFirmId = $user->isSuperAdmin() ? null : $user->builder_firm_id;
        $filters = $request->only(['date_from', 'date_to']);
        $data = $this->reportService->cpPerformanceReport($builderFirmId, $filters);

        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ]);
    }

    public function conversion(Request $request): JsonResponse
    {
        $user = $request->user();
        $builderFirmId = $user->isSuperAdmin() ? null : $user->builder_firm_id;
        $filters = $request->only(['date_from', 'date_to']);
        $data = $this->reportService->conversionReport($builderFirmId, $filters);

        return response()->json([
            'data' => $data,
            'message' => 'Success',
        ]);
    }
}
