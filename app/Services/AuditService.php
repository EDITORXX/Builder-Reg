<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public function log(
        int $actorId,
        string $action,
        string $entityType,
        int $entityId,
        ?array $before = null,
        ?array $after = null,
        ?string $notes = null,
        ?Request $request = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'ip_address' => $request?->ip(),
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }
}
