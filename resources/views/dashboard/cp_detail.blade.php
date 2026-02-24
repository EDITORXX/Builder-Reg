@extends('layouts.app')

@php
    $cpName = $channelPartner->user?->name ?? $channelPartner->firm_name ?? 'Channel Partner';
    $rankLabel = null;
    if ($rank !== null) {
        $rankLabel = match ($rank) {
            1 => '1st',
            2 => '2nd',
            3 => '3rd',
            default => 'No. ' . $rank,
        };
    }
@endphp
@section('title', $cpName)
@section('heading', $cpName)
@section('subtitle', 'Channel partner details')

@section('content')
    <div style="margin-bottom: 1rem; font-size: 0.875rem;">
        <a href="{{ route('tenant.cp-applications.index', ['slug' => $tenant->slug]) }}">Channel Partners</a>
        <span style="color: var(--text-secondary);"> / </span>
        <span>{{ $cpName }}</span>
    </div>

    @if(session('success'))
        <div class="card" style="margin-bottom: 1rem; border-color: var(--success); background: #f0fdf4;">
            <div class="card-body">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="card" style="margin-bottom: 1rem; border-color: var(--error); background: #fef2f2;">
            <div class="card-body">{{ session('error') }}</div>
        </div>
    @endif
    @if(session('show_cp_password') && session('cp_password_value'))
        <div class="card password-reveal-card" style="margin-bottom: 1rem; border-color: var(--accent); background: #eff6ff;">
            <div class="card-body">
                <p style="margin: 0 0 0.5rem 0; font-weight: 600;">New password (copy now — won’t be shown again)</p>
                <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: var(--text-secondary);">{{ session('cp_password_name') }} — {{ session('cp_password_email') }}</p>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <code id="new-cp-password-value" style="padding: 0.5rem 0.75rem; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem;">{{ session('cp_password_value') }}</code>
                    <button type="button" onclick="copyNewCpPassword()" class="btn-primary" style="cursor: pointer;">Copy</button>
                </div>
            </div>
        </div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Details</h2>
            <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Application status: {{ $cpApplication->status }} · Applied {{ $cpApplication->created_at?->format('M j, Y') ?? '—' }}</p>
        </div>
        <div class="card-body">
            <dl style="display: grid; gap: 0.5rem 1.5rem; font-size: 0.875rem; margin: 0;">
                <dt style="font-weight: 600;">Name</dt>
                <dd style="margin: 0;">{{ $channelPartner->user?->name ?? '—' }}</dd>
                <dt style="font-weight: 600;">Email</dt>
                <dd style="margin: 0;">{{ $channelPartner->user?->email ?? '—' }}</dd>
                @if($user->isSuperAdmin() || $user->isBuilderAdmin())
                <dt style="font-weight: 600;">Password</dt>
                <dd style="margin: 0;">
                    @if($channelPartner->user)
                        <form method="POST" action="{{ route('tenant.channel-partners.reset-password', [$tenant->slug, $channelPartner]) }}" style="display: inline;" onsubmit="return confirm('Generate a new password for this channel partner? Their current password will stop working. You will see the new password once.');">
                            @csrf
                            <button type="submit" class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Reset password</button>
                        </form>
                    @else
                        —
                    @endif
                </dd>
                @endif
                <dt style="font-weight: 600;">Firm</dt>
                <dd style="margin: 0;">{{ $channelPartner->firm_name ?? '—' }}</dd>
                <dt style="font-weight: 600;">RERA number</dt>
                <dd style="margin: 0;">{{ $channelPartner->rera_number ?? '—' }}</dd>
                <dt style="font-weight: 600;">PAN</dt>
                <dd style="margin: 0;">{{ $channelPartner->pan_number ?? '—' }}</dd>
                <dt style="font-weight: 600;">GST number</dt>
                <dd style="margin: 0;">{{ $channelPartner->gst_number ?? '—' }}</dd>
                @if(!empty($channelPartner->documents) && is_array($channelPartner->documents))
                <dt style="font-weight: 600;">Documents</dt>
                <dd style="margin: 0;">
                    @foreach($channelPartner->documents as $doc)
                        @if(is_string($doc))
                            <span style="display: block;">{{ $doc }}</span>
                        @elseif(is_array($doc) && isset($doc['url']))
                            <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" style="display: block;">{{ $doc['name'] ?? 'Document' }}</a>
                        @endif
                    @endforeach
                </dd>
                @endif
                @if(isset($managers) && $managers->isNotEmpty() && ($user->isSuperAdmin() || $user->isBuilderAdmin()))
                <dt style="font-weight: 600;">Assigned manager</dt>
                <dd style="margin: 0;">
                    <form method="POST" action="{{ route('tenant.cp-applications.assign-manager', [$tenant->slug, $cpApplication]) }}" style="display: inline;">
                        @csrf
                        <select name="manager_id" onchange="this.form.submit()" style="padding: 0.375rem 0.5rem; font-size: 0.875rem; border-radius: var(--radius); min-width: 160px;">
                            <option value="">— None —</option>
                            @foreach($managers as $m)
                                <option value="{{ $m->id }}" {{ (int)($cpApplication->manager_id ?? 0) === (int)$m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </dd>
                @elseif($cpApplication->manager)
                <dt style="font-weight: 600;">Assigned manager</dt>
                <dd style="margin: 0;">{{ $cpApplication->manager->name }}</dd>
                @endif
            </dl>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Performance</h2>
        </div>
        <div class="card-body">
            <div class="stat-grid" style="margin-bottom: 0;">
                <div class="stat-card">
                    <div class="stat-label">Leads</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $leads_count ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Visits</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $visit_done_count ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Meetings</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $meetings_count ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">QR generated</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $qr_generated_count ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Rank</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $rankLabel ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Leads</h2>
            <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Leads brought by this channel partner for your projects.</p>
        </div>
        <div class="card-body">
            @if($leads->isEmpty())
                <p style="margin: 0; color: var(--text-secondary);">No leads yet.</p>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Customer</th>
                                <th style="padding: 0.5rem 0;">Project</th>
                                <th style="padding: 0.5rem 0;">Status</th>
                                <th style="padding: 0.5rem 0;">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ $lead->customer?->name ?? '—' }}<br><span style="font-size: 0.8125rem; color: var(--text-secondary);">{{ $lead->customer?->mobile ?? '' }}</span></td>
                                    <td style="padding: 0.5rem 0;">{{ $lead->project?->name ?? '—' }}</td>
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $lead->sales_status ?? '—') }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $lead->created_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($leads->hasPages())
                    <div style="margin-top: 1rem;">
                        {{ $leads->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    @if(session('show_cp_password') && session('cp_password_value'))
    <script>
        function copyNewCpPassword() {
            var el = document.getElementById('new-cp-password-value');
            if (!el) return;
            navigator.clipboard.writeText(el.textContent).then(function() {
                var btn = document.querySelector('.password-reveal-card button');
                if (btn) { btn.textContent = 'Copied!'; setTimeout(function() { btn.textContent = 'Copy'; }, 1500); }
            });
        }
    </script>
    @endif
@endsection
