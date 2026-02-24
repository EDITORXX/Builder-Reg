<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\VisitSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireScheduledVisitsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        $graceHours = (int) config('visit_schedule.token_grace_hours', 24);
        $cutoff = now()->subHours($graceHours);

        $expired = VisitSchedule::where('status', VisitSchedule::STATUS_SCHEDULED)
            ->where('scheduled_at', '<', $cutoff)
            ->get();

        foreach ($expired as $schedule) {
            $schedule->update(['status' => VisitSchedule::STATUS_EXPIRED]);

            if ($schedule->lead_id) {
                $this->maybeSetLeadVisitCancelled($schedule);
            }
        }
    }

    private function maybeSetLeadVisitCancelled(VisitSchedule $schedule): void
    {
        $lead = Lead::find($schedule->lead_id);
        if (! $lead) {
            return;
        }

        $hasLaterSchedule = VisitSchedule::where('lead_id', $lead->id)
            ->where('id', '!=', $schedule->id)
            ->where('scheduled_at', '>', $schedule->scheduled_at)
            ->whereNotIn('status', [VisitSchedule::STATUS_EXPIRED])
            ->exists();

        if (! $hasLaterSchedule) {
            $lead->update(['visit_status' => Lead::VISIT_CANCELLED]);
        }
    }
}
