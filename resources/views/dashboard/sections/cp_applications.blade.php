@php
    $statusFilter = request('status', 'pending');
@endphp
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Pending CP / Channel Partners</h2>
        <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Verify CP applications. Only approved CPs appear in the customer registration form.</p>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
        @endif
        <div style="margin-bottom: 1rem;">
            <a href="{{ route('tenant.cp-applications.index', ['slug' => $tenant->slug, 'status' => 'pending']) }}" style="margin-right: 0.75rem; font-size: 0.875rem; {{ $statusFilter === 'pending' ? 'font-weight: 600;' : '' }}">Pending</a>
            <a href="{{ route('tenant.cp-applications.index', ['slug' => $tenant->slug, 'status' => 'approved']) }}" style="margin-right: 0.75rem; font-size: 0.875rem; {{ $statusFilter === 'approved' ? 'font-weight: 600;' : '' }}">Approved</a>
            <a href="{{ route('tenant.cp-applications.index', ['slug' => $tenant->slug, 'status' => 'rejected']) }}" style="font-size: 0.875rem; {{ $statusFilter === 'rejected' ? 'font-weight: 600;' : '' }}">Rejected</a>
        </div>

        @if($cpApplications->isEmpty())
            <p style="margin: 0; color: var(--text-secondary);">No applications in this category.</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Name</th>
                            <th style="padding: 0.5rem 0;">Firm / Email</th>
                            <th style="padding: 0.5rem 0;">Status</th>
                            <th style="padding: 0.5rem 0;">Manager</th>
                            <th style="padding: 0.5rem 0;">Applied</th>
                            <th style="padding: 0.5rem 0;">View</th>
                            @if($statusFilter === 'pending')
                                <th style="padding: 0.5rem 0;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cpApplications as $app)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $app->channelPartner?->user?->name ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">{{ $app->channelPartner?->firm_name ?? '—' }}<br><span style="font-size: 0.8125rem; color: var(--text-secondary);">{{ $app->channelPartner?->user?->email ?? '' }}</span></td>
                                <td style="padding: 0.5rem 0;">{{ $app->status }}</td>
                                <td style="padding: 0.5rem 0;">
                                    @if($app->status === 'approved' && isset($managers) && $managers->isNotEmpty())
                                        <form method="POST" action="{{ route('tenant.cp-applications.assign-manager', [$tenant->slug, $app]) }}" style="display: inline;">
                                            @csrf
                                            <select name="manager_id" onchange="this.form.submit()" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; border-radius: var(--radius); min-width: 120px;">
                                                <option value="">— None —</option>
                                                @foreach($managers as $m)
                                                    <option value="{{ $m->id }}" {{ (int)($app->manager_id ?? 0) === (int)$m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        {{ $app->manager?->name ?? '—' }}
                                    @endif
                                </td>
                                <td style="padding: 0.5rem 0;">{{ $app->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">
                                    @if($app->channelPartner)
                                        <a href="{{ route('tenant.channel-partners.show', [$tenant->slug, $app->channelPartner]) }}" style="font-size: 0.875rem;">View</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                @if($statusFilter === 'pending')
                                    <td style="padding: 0.5rem 0;">
                                        <form method="POST" action="{{ route('tenant.cp-applications.approve', [$tenant->slug, $app]) }}" style="display: inline-block; margin-right: 0.5rem;">
                                            @csrf
                                            <button type="submit" class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem;">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('tenant.cp-applications.reject', [$tenant->slug, $app]) }}" style="display: inline-block;">
                                            <input type="text" name="notes" placeholder="Rejection reason" required style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; width: 140px; margin-right: 0.25rem;">
                                            <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.8125rem; color: var(--error); background: none; border: none; cursor: pointer;">Reject</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($cpApplications->hasPages())
                <div style="margin-top: 1rem;">
                    {{ $cpApplications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

