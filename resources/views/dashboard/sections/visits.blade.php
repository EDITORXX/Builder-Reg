<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
        @endif

        @if($visits->isEmpty())
            <p style="margin: 0; color: var(--text-secondary);">No visits yet.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Lead / Customer</th>
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">Scheduled at</th>
                            <th style="padding: 0.5rem 0;">Status</th>
                            <th style="padding: 0.5rem 0;">Confirm method</th>
                            <th style="padding: 0.5rem 0;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $visit)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $visit->lead?->customer?->name ?? '—' }}<br><span style="font-size: 0.8125rem; color: var(--text-secondary);">{{ $visit->lead?->customer?->mobile ?? '' }}</span></td>
                                <td style="padding: 0.5rem 0;">{{ $visit->lead?->project?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $visit->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $visit->status ?? '—') }}</td>
                                <td style="padding: 0.5rem 0;">{{ $visit->confirm_method ? strtoupper($visit->confirm_method) : '—' }}</td>
                                <td style="padding: 0.5rem 0;">
                                    @if(in_array($visit->status, [\App\Models\Visit::STATUS_SCHEDULED, \App\Models\Visit::STATUS_RESCHEDULED]))
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                            <form method="POST" action="{{ route('tenant.visits.reschedule', [$tenant->slug, $visit]) }}" style="display: inline-flex; gap: 0.25rem; align-items: center;">
                                                @csrf
                                                @method('PATCH')
                                                <input type="datetime-local" name="scheduled_at" value="{{ $visit->scheduled_at?->format('Y-m-d\TH:i') }}" required style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; border: 1px solid var(--border); border-radius: var(--radius);">
                                                <button type="submit" style="font-size: 0.8125rem;">Reschedule</button>
                                            </form>
                                            <form method="POST" action="{{ route('tenant.visits.cancel', [$tenant->slug, $visit]) }}" style="display: inline;" onsubmit="return confirm('Cancel this visit?');">
                                                @csrf
                                                <input type="text" name="reason" placeholder="Reason (optional)" maxlength="500" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; width: 120px; border: 1px solid var(--border); border-radius: var(--radius); margin-right: 0.25rem;">
                                                <button type="submit" style="font-size: 0.8125rem; color: var(--error); background: none; border: none; cursor: pointer;">Cancel</button>
                                            </form>
                                            @if($canSendOtp ?? false)
                                                <form method="POST" action="{{ route('tenant.visits.otp.send', [$tenant->slug, $visit]) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" style="font-size: 0.8125rem;">Send OTP</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('tenant.visits.confirm', [$tenant->slug, $visit]) }}" style="display: inline;" onsubmit="return confirm('Manually confirm this visit? Lock will be created.');">
                                                @csrf
                                                <input type="text" name="notes" placeholder="Notes (optional)" maxlength="500" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; width: 100px; border: 1px solid var(--border); border-radius: var(--radius); margin-right: 0.25rem;">
                                                <button type="submit" style="font-size: 0.8125rem;">Manual confirm</button>
                                            </form>
                                        </div>
                                        @if(($canSendOtp ?? false) && $visit->visitOtp && !$visit->visitOtp->verified_at)
                                            <form method="POST" action="{{ route('tenant.visits.otp.verify', [$tenant->slug, $visit]) }}" style="display: inline-flex; gap: 0.25rem; margin-top: 0.25rem;">
                                                @csrf
                                                <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" size="6" pattern="[0-9]{6}" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; width: 5rem; border: 1px solid var(--border); border-radius: var(--radius);">
                                                <button type="submit" style="font-size: 0.8125rem;">Verify OTP</button>
                                            </form>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($visits->hasPages())
                <div style="margin-top: 1rem;">
                    {{ $visits->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
