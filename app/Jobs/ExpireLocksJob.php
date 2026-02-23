<?php

namespace App\Jobs;

use App\Models\LeadLock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExpireLocksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(): void
    {
        LeadLock::where('status', LeadLock::STATUS_ACTIVE)
            ->where('end_at', '<', now())
            ->update(['status' => LeadLock::STATUS_EXPIRED]);
    }
}
