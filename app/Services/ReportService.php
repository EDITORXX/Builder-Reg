<?php

namespace App\Services;

use App\Models\ChannelPartner;
use App\Models\CpApplication;
use App\Models\Lead;
use App\Models\LeadLock;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * @param int|null $builderFirmId null = all (super_admin), otherwise scope to builder
     * @param array{date_from?: string, date_to?: string, project_id?: int, status?: string} $filters
     * @return array{by_project: Collection, by_status: Collection}
     */
    public function leadsReport(?int $builderFirmId, array $filters): array
    {
        $query = Lead::with(['project', 'customer', 'channelPartner']);
        if ($builderFirmId !== null) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId));
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $byProject = (clone $query)->selectRaw('project_id, count(*) as total')->groupBy('project_id')->get();
        $byStatus = (clone $query)->selectRaw('status, count(*) as total')->groupBy('status')->get();

        return [
            'by_project' => $byProject,
            'by_status' => $byStatus,
        ];
    }

    /**
     * @param int|null $builderFirmId null = all (super_admin)
     * @param array{project_id?: int} $filters
     * @return Collection
     */
    public function locksReport(?int $builderFirmId, array $filters): Collection
    {
        $query = LeadLock::with(['project', 'lead', 'channelPartner'])->active();
        if ($builderFirmId !== null) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId));
        }
        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        return $query->orderBy('end_at')->get();
    }

    /**
     * @param int|null $builderFirmId null = all (super_admin)
     * @param array{date_from?: string, date_to?: string} $filters
     * @return Collection
     */
    public function cpPerformanceReport(?int $builderFirmId, array $filters): Collection
    {
        $query = Lead::with('channelPartner');
        if ($builderFirmId !== null) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId));
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
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
        $cpIds = $perCp->pluck('channel_partner_id')->filter()->unique()->values()->all();
        $cps = ChannelPartner::with('user')->whereIn('id', $cpIds)->get()->keyBy('id');
        foreach ($perCp as $row) {
            $row->booked_count = $bookedPerCp->get($row->channel_partner_id)?->booked_count ?? 0;
            $cp = $cps->get($row->channel_partner_id);
            $row->cp_name = $cp ? ($cp->user?->name ?? $cp->firm_name ?? '—') : '—';
        }

        return $perCp;
    }

    /**
     * @param int|null $builderFirmId null = all (super_admin)
     * @param array{date_from?: string, date_to?: string} $filters
     * @return array{visit_done_count: int, booked_count: int, conversion_rate_percent: float}
     */
    public function conversionReport(?int $builderFirmId, array $filters): array
    {
        $query = Lead::with('project');
        if ($builderFirmId !== null) {
            $query->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId));
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        $visitDone = (clone $query)->where('status', Lead::STATUS_VISIT_DONE)->count();
        $booked = (clone $query)->where('status', Lead::STATUS_BOOKED)->count();
        $rate = $visitDone > 0 ? round($booked / $visitDone * 100, 2) : 0;

        return [
            'visit_done_count' => $visitDone,
            'booked_count' => $booked,
            'conversion_rate_percent' => $rate,
        ];
    }

    /**
     * CP ranking by visit_done lead count for a builder.
     * Includes all CPs that have an application with this builder.
     *
     * @return Collection<int, object{channel_partner_id: int|null, visit_done_count: int, rank: int}>
     */
    public function getCpVisitDoneRanking(int $builderFirmId): Collection
    {
        $cpIds = CpApplication::where('builder_firm_id', $builderFirmId)
            ->distinct()
            ->pluck('channel_partner_id');

        $visitDoneCounts = Lead::whereHas('project', fn ($q) => $q->where('builder_firm_id', $builderFirmId))
            ->where('status', Lead::STATUS_VISIT_DONE)
            ->whereNotNull('channel_partner_id')
            ->selectRaw('channel_partner_id, count(*) as visit_done_count')
            ->groupBy('channel_partner_id')
            ->get()
            ->keyBy('channel_partner_id');

        $rows = $cpIds->map(function ($cpId) use ($visitDoneCounts) {
            $count = (int) ($visitDoneCounts->get($cpId)?->visit_done_count ?? 0);
            return (object) [
                'channel_partner_id' => $cpId,
                'visit_done_count' => $count,
            ];
        })
            ->sort(function ($a, $b) {
                if ($a->visit_done_count !== $b->visit_done_count) {
                    return $b->visit_done_count <=> $a->visit_done_count;
                }
                return ($a->channel_partner_id <=> $b->channel_partner_id);
            })
            ->values();

        $rank = 1;
        return $rows->map(function ($row) use (&$rank) {
            $row->rank = $rank++;
            return $row;
        });
    }
}
