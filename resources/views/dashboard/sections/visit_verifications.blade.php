@php
    $pendingLeads = $pendingLeads ?? collect();
    $visitSchedulesByLeadId = $visitSchedulesByLeadId ?? collect();
@endphp
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel ?? 'Pending Visit Verifications' }}</h2>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
        @endif

        @if($pendingLeads->isEmpty())
            <p style="margin: 0; color: var(--text-secondary);">No pending visit verifications.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Customer</th>
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">CP</th>
                            <th style="padding: 0.5rem 0;">Visit type</th>
                            <th style="padding: 0.5rem 0;">Scheduled</th>
                            <th style="padding: 0.5rem 0;">Submitted</th>
                            <th style="padding: 0.5rem 0;">Photo</th>
                            <th style="padding: 0.5rem 0;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingLeads as $lead)
                            @php
                                $vs = $visitSchedulesByLeadId[$lead->id] ?? null;
                                $pendingCheckIn = $lead->visitCheckIns->first();
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $lead->customer?->name ?? '—' }}<br><span style="font-size: 0.8125rem;">{{ $lead->customer?->mobile ?? '' }}</span></td>
                                <td style="padding: 0.5rem 0;">{{ $lead->project?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $lead->channelPartner?->firm_name ?? $lead->channelPartner?->user?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $pendingCheckIn?->visit_type === 'scheduled_checkin' ? 'Scheduled' : 'Direct' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $vs?->scheduled_at?->format('M j, H:i') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $pendingCheckIn?->submitted_at?->format('M j, H:i') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">
                                    @if($pendingCheckIn?->visit_photo_path ?? $lead->visit_photo_path)
                                        <a href="{{ route('tenant.leads.visit-photo', [$tenant->slug, $lead]) }}" target="_blank" rel="noopener" style="font-size: 0.8125rem;">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="padding: 0.5rem 0;">
                                    <form method="POST" action="{{ route('tenant.visit-verifications.approve', [$tenant->slug, $lead]) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" style="font-size: 0.8125rem; padding: 0.25rem 0.5rem; margin-right: 0.25rem;">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('tenant.visit-verifications.reject', [$tenant->slug, $lead]) }}" style="display: inline;" onsubmit="return confirm('Reject this visit?');">
                                        @csrf
                                        <input type="text" name="reason" placeholder="Reason (optional)" maxlength="1000" style="width: 120px; padding: 0.25rem 0.5rem; font-size: 0.8125rem; border: 1px solid var(--border); border-radius: var(--radius); margin-right: 0.25rem;">
                                        <button type="submit" style="font-size: 0.8125rem; color: var(--error); background: none; border: none; cursor: pointer;">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pendingLeads->hasPages())
                <div style="margin-top: 1rem;">{{ $pendingLeads->links() }}</div>
            @endif
        @endif
    </div>
</div>
