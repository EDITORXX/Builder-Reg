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
            <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
                <a href="{{ route('tenants.create') }}">New tenant</a> ·
                <a href="{{ route('tenants.index') }}">Tenants</a> ·
                <a href="{{ route('plans.index') }}">Plans</a>
            </p>
        </div>
    </div>
    @endif

    @if(isset($tenant) && $tenant && isset($stats))
    {{-- Builder / Tenant: plan limits, usage, leads, conversion --}}
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">{{ $tenant->name }}</h2>
            <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">{{ $tenant->plan?->name ?? '—' }}</p>
        </div>
        <div class="card-body">
            <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Plan usage</h3>
            <div class="stat-grid" style="margin-bottom: 1rem;">
                <div class="stat-card">
                    <div class="stat-label">Users</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['usage']['users_count'] ?? 0 }} / {{ $stats['plan_limits']['max_users'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Projects</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['usage']['projects_count'] ?? 0 }} / {{ $stats['plan_limits']['max_projects'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Channel partners</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['usage']['channel_partners_count'] ?? 0 }} / {{ $stats['plan_limits']['max_channel_partners'] ?? 0 }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Leads</div>
                    <div class="stat-value" style="font-size:1.125rem;">{{ $stats['usage']['leads_count'] ?? 0 }} / {{ $stats['plan_limits']['max_leads'] ?? 0 }}</div>
                </div>
            </div>

            <h3 style="font-size: 1rem; margin: 1rem 0 0.75rem 0;">Leads by status</h3>
            <div class="stat-grid" style="margin-bottom: 1rem;">
                @php
                    $statusLabels = [
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'visit_scheduled' => 'Visit scheduled',
                        'visit_done' => 'Visit done',
                        'negotiation' => 'Negotiation',
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
                            <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $lead->status ?? '—') }}</td>
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
