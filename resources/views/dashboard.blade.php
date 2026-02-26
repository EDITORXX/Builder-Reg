@extends('layouts.app')

@php
    $section = $section ?? null;
    $sectionLabels = [
        'projects' => 'Projects',
        'leads' => 'Leads',
        'settings' => 'Settings',
        'cp-applications' => 'Channel Partners',
        'managers' => 'Managers',
        'forms' => 'Forms',
        'locks' => 'Locks',
        'visits' => 'Visits',
        'visit-verifications' => 'Pending Visit Verifications',
        'reports' => 'Reports',
        'profile' => 'Profile',
    ];
    $sectionLabel = $section ? ($sectionLabels[$section] ?? ucfirst(str_replace('-', ' ', $section))) : null;
@endphp
@section('title', $sectionLabel ? $sectionLabel : 'Dashboard')
@section('heading', $sectionLabel ? $sectionLabel : 'Dashboard')
@section('subtitle', $sectionLabel ? '' : 'Welcome back. Here’s your account overview.')

@section('content')
    @if($section)
        @include('dashboard.sections.' . str_replace('-', '_', $section), ['sectionLabel' => $sectionLabel])
    @else
    <div class="dashboard-welcome"></div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Role</div>
            <div class="stat-value">
                @php
                    $roleClass = match($user->role ?? '') {
                        'super_admin' => 'super_admin',
                        'builder_admin' => 'builder_admin',
                        default => '',
                    };
                @endphp
                <span class="role-badge {{ $roleClass }}">{{ str_replace('_', ' ', $user->role ?? 'user') }}</span>
            </div>
        </div>
        @if(isset($tenant) && $tenant)
        <div class="stat-card">
            <div class="stat-label">Builder</div>
            <div class="stat-value" style="font-size:1rem;">{{ $tenant->name }}</div>
        </div>
        @elseif($user->builderFirm ?? null)
        <div class="stat-card">
            <div class="stat-label">Builder</div>
            <div class="stat-value" style="font-size:1rem;">{{ $user->builderFirm->name }}</div>
        </div>
        @endif
    </div>

    @if(isset($stats) && $user->role === 'super_admin' && !isset($tenant))
    {{-- Super Admin: platform-level stats and quick links --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Platform overview</h2>
        </div>
        <div class="card-body">
            <div class="stat-grid" style="margin-bottom: 1rem;">
                <div class="stat-card">
                    <div class="stat-label">Total tenants</div>
                    <div class="stat-value">{{ $stats['total_tenants'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Active tenants</div>
                    <div class="stat-value">{{ $stats['active_tenants'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Plans</div>
                    <div class="stat-value">{{ $stats['total_plans'] ?? 0 }}</div>
                </div>
            </div>
            <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Leads by sales status (all tenants)</h3>
            <div class="stat-grid" style="margin-bottom: 1rem;">
                @php
                    $statusLabels = [
                        'new' => 'New',
                        'negotiation' => 'Negotiation',
                        'hold' => 'Hold',
                        'booked' => 'Booked',
                        'lost' => 'Lost',
                    ];
                    $leadsByStatus = $stats['leads_by_status'] ?? collect();
                @endphp
                @foreach($statusLabels as $key => $label)
                <div class="stat-card">
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $leadsByStatus->get($key)?->total ?? 0 }}</div>
                </div>
                @endforeach
            </div>
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                <a href="{{ route('tenants.create') }}">New tenant</a> ·
                <a href="{{ route('tenants.index') }}">Tenants</a> ·
                <a href="{{ route('plans.index') }}">Plans</a>
            </p>
            @if(config('app.system_actions_enabled', true))
            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: 600;">System</p>
                <form method="POST" action="{{ route('system.git-push') }}" style="display: inline-block; margin-right: 0.5rem;" onsubmit="return confirm('Push current code to Git?');">
                    @csrf
                    <button type="submit" class="btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Push to Git</button>
                </form>
                <form method="POST" action="{{ route('system.migrate') }}" style="display: inline-block;" onsubmit="return confirm('Run database migrations?');">
                    @csrf
                    <button type="submit" style="padding: 0.25rem 0.5rem; font-size: 0.875rem; border: 1px solid var(--border); border-radius: var(--radius); background: var(--bg-card); cursor: pointer;">Run migrations</button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if(isset($tenant) && $tenant && isset($stats))
    {{-- Builder / Tenant: leads, conversion --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">{{ $tenant->name }}</h2>
        </div>
        <div class="card-body">
            <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Leads by sales status</h3>
            <div class="stat-grid" style="margin-bottom: 1rem;">
                @php
                    $statusLabels = [
                        'new' => 'New',
                        'negotiation' => 'Negotiation',
                        'hold' => 'Hold',
                        'booked' => 'Booked',
                        'lost' => 'Lost',
                    ];
                    $leadsByStatus = $stats['leads_by_status'] ?? collect();
                @endphp
                @foreach($statusLabels as $key => $label)
                <div class="stat-card">
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $leadsByStatus->get($key)?->total ?? 0 }}</div>
                </div>
                @endforeach
            </div>

            <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Conversion</h3>
            <div class="stat-grid" style="margin-bottom: 1rem;">
                <div class="stat-card">
                    <div class="stat-label">Visit done</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['conversion']['visit_done_count'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Booked</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['conversion']['booked_count'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Conversion rate</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ number_format($stats['conversion']['conversion_rate_percent'] ?? 0, 1) }}%</div>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.875rem;">
                <span><strong>Active locks:</strong> {{ $stats['active_locks_count'] ?? 0 }}</span>
                @if(($stats['cp_applications_pending_count'] ?? 0) > 0)
                <span><strong>Pending CP applications:</strong> {{ $stats['cp_applications_pending_count'] }}</span>
                @endif
            </div>

            @if(isset($stats['recent_leads']) && $stats['recent_leads']->isNotEmpty())
            <h3 style="font-size: 1rem; margin: 1.25rem 0 0.75rem 0;">Recent leads</h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Project</th>
                            <th style="padding: 0.5rem 0;">Status</th>
                            <th style="padding: 0.5rem 0;">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['recent_leads']->take(5) as $lead)
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 0.5rem 0;">{{ $lead->project?->name ?? '—' }}</td>
                            <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $lead->sales_status ?? '—') }}</td>
                            <td style="padding: 0.5rem 0;">{{ $lead->created_at?->format('M j, Y') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="card profile-card">
        <div class="card-header">
            <h2 class="card-title">Account details</h2>
        </div>
        <div class="card-body">
            <dl class="profile-row">
                <dt>Name</dt>
                <dd>{{ $user->name ?? '—' }}</dd>
            </dl>
            <dl class="profile-row">
                <dt>Email</dt>
                <dd>{{ $user->email ?? '—' }}</dd>
            </dl>
            <dl class="profile-row">
                <dt>Role</dt>
                <dd>{{ str_replace('_', ' ', ucfirst($user->role ?? '—')) }}</dd>
            </dl>
            @if(isset($tenant) && $tenant)
            <dl class="profile-row">
                <dt>Builder firm</dt>
                <dd>{{ $tenant->name }}</dd>
            </dl>
            @elseif($user->builderFirm ?? null)
            <dl class="profile-row">
                <dt>Builder firm</dt>
                <dd>{{ $user->builderFirm->name }}</dd>
            </dl>
            @endif
        </div>
    </div>
    @endif
@endsection
