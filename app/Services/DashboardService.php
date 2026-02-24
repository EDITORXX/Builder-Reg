<?php

namespace App\Services;

use App\Models\BuilderFirm;
use App\Models\CpApplication;
use App\Models\Lead;
use App\Models\LeadLock;
use App\Models\Plan;

class DashboardService
{
    /**
     * Platform-level stats for Super Admin dashboard.
     *
     * @return array{total_tenants: int, active_tenants: int, total_plans: int}
     */
    public function getSuperAdminStats(): array
    {
        return [
            'total_tenants' => BuilderFirm::count(),
            'active_tenants' => BuilderFirm::where('is_active', true)->count(),
            'total_plans' => Plan::count(),
        ];
    }

    /**
     * Tenant-level stats for Builder Admin (and manager/sales_exec/viewer) dashboard.
     *
     * @return array{
     *   plan_limits: array{max_users: int, max_projects: int, max_channel_partners: int, max_leads: int},
     *   usage: array{users_count: int, projects_count: int, channel_partners_count: int, leads_count: int},
     *   leads_by_status: \Illuminate\Support\Collection,
     *   conversion: array{visit_done_count: int, booked_count: int, conversion_rate_percent: float},
     *   active_locks_count: int,
     *   recent_leads: \Illuminate\Database\Eloquent\Collection,
     *   cp_applications_pending_count: int
     * }
     */
    public function getTenantDashboardStats(BuilderFirm $builder): array
    {
        $builderId = $builder->id;

        $leadQuery = Lead::query()->whereHas(
            'project',
            fn ($q) => $q->where('builder_firm_id', $builderId)
        );

        $byStatus = (clone $leadQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $visitDone = (clone $leadQuery)->where('status', Lead::STATUS_VISIT_DONE)->count();
        $booked = (clone $leadQuery)->where('status', Lead::STATUS_BOOKED)->count();
        $conversionRate = $visitDone > 0 ? round($booked / $visitDone * 100, 2) : 0.0;

        $activeLocksCount = LeadLock::query()
            ->active()
            ->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderId))
            ->count();

        $recentLeads = (clone $leadQuery)
            ->with(['project', 'customer'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $cpPendingCount = $builder->cpApplications()
            ->where('status', CpApplication::STATUS_PENDING)
            ->count();

        return [
            'plan_limits' => [
                'max_users' => $builder->getMaxUsers(),
                'max_projects' => $builder->getMaxProjects(),
                'max_channel_partners' => $builder->getMaxChannelPartners(),
                'max_leads' => $builder->getMaxLeads(),
            ],
            'usage' => [
                'users_count' => $builder->users()->count(),
                'projects_count' => $builder->projects()->count(),
                'channel_partners_count' => $builder->cpApplications()
                    ->where('status', CpApplication::STATUS_APPROVED)
                    ->count(),
                'leads_count' => (clone $leadQuery)->count(),
            ],
            'leads_by_status' => $byStatus,
            'conversion' => [
                'visit_done_count' => $visitDone,
                'booked_count' => $booked,
                'conversion_rate_percent' => $conversionRate,
            ],
            'active_locks_count' => $activeLocksCount,
            'recent_leads' => $recentLeads,
            'cp_applications_pending_count' => $cpPendingCount,
        ];
    }
}
