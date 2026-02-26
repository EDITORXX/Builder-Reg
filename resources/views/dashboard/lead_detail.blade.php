@extends('layouts.app')

@php
    $customerName = $lead->customer?->name ?? '—';
    $customerMobile = $lead->customer?->mobile ?? '';
    $salesLabel = $lead->sales_status ? str_replace('_', ' ', ucfirst($lead->sales_status)) : '—';
    $visitLabel = $lead->visit_status ? str_replace('_', ' ', ucfirst($lead->visit_status)) : '—';
    $verificationLabel = match ($lead->verification_status ?? '') {
        'pending_verification' => 'Pending verification',
        'verified_visit' => 'Verified',
        'rejected_verification' => 'Rejected',
        default => $lead->verification_status ? str_replace('_', ' ', ucfirst($lead->verification_status)) : '—',
    };
@endphp
@section('title', 'Lead Details')
@section('heading', 'Lead Details')
@section('subtitle', $customerName)

@section('content')
    <div style="margin-bottom: 1rem; font-size: 0.875rem;">
        @if($user->isChannelPartner())
            <a href="{{ route('cp.leads') }}">← Back to Leads</a>
        @else
            <a href="{{ route('tenant.leads.index', $tenant->slug) }}">← Back to Leads</a>
        @endif
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <div style="display: flex; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div style="width: 3rem; height: 3rem; border-radius: 50%; background: var(--border); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 600; flex-shrink: 0;">
                    {{ strtoupper(substr($customerName, 0, 1)) ?: '?' }}
                </div>
                <div>
                    <h2 style="margin: 0 0 0.25rem 0; font-size: 1.25rem;">{{ $customerName }}</h2>
                    <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Mobile: {{ $customerMobile ?: '—' }}</p>
                    <div style="margin-top: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);">Sale: {{ $salesLabel }}</span>
                        <span style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);">Visit: {{ $visitLabel }}</span>
                        <span style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius);">Verification: {{ $verificationLabel }}</span>
                    </div>
                </div>
            </div>
            <p style="margin: 1rem 0 0; font-size: 0.875rem; color: var(--text-secondary);"><strong>Visit type:</strong> {{ $visitTypeLabel }}</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Lead information</h2>
        </div>
        <div class="card-body">
            <dl style="display: grid; gap: 0.5rem 1rem; font-size: 0.875rem; margin: 0; grid-template-columns: auto 1fr;">
                <dt style="font-weight: 600;">Project</dt>
                <dd style="margin: 0;">{{ $lead->project?->name ?? '—' }}</dd>
                <dt style="font-weight: 600;">Channel Partner</dt>
                <dd style="margin: 0;">{{ $lead->channelPartner?->user?->name ?? $lead->channelPartner?->firm_name ?? '—' }}</dd>
                <dt style="font-weight: 600;">Created</dt>
                <dd style="margin: 0;">{{ $lead->created_at?->format('M j, Y') ?? '—' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">Visit history / Activity timeline</h2>
        </div>
        <div class="card-body">
            @php $timeline = $timeline ?? []; @endphp
            @if(empty($timeline))
                <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">No visit or activity history yet.</p>
            @else
                @php
                    $grouped = collect($timeline)->groupBy(fn ($e) => $e['date']->format('M j, Y'));
                @endphp
                @foreach($grouped as $dateLabel => $items)
                    @if($loop->first)
                        <h3 style="font-size: 0.875rem; margin: 0 0 0.5rem 0; color: var(--text-secondary);">{{ $dateLabel }}</h3>
                    @else
                        <h3 style="font-size: 0.875rem; margin: 1.25rem 0 0.5rem 0; color: var(--text-secondary);">{{ $dateLabel }}</h3>
                    @endif
                    <ul style="list-style: none; padding: 0; margin: 0 0 0.5rem 0;">
                        @foreach($items as $item)
                            <li style="padding: 0.35rem 0; font-size: 0.875rem; border-bottom: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 0.25rem 0.5rem;">
                                <span style="color: var(--text-secondary); flex-shrink: 0;">{{ $item['date']->format('g:i A') }}</span>
                                <span>{{ $item['label'] }}</span>
                                <span style="color: var(--text-secondary);">– by {{ $item['actor'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            @endif
        </div>
    </div>

    @if($lead->visit_photo_path || $lead->visitCheckIns->where('visit_photo_path', '!=', null)->first())
        <div style="margin-bottom: 1.5rem;">
            <a href="{{ route('tenant.leads.visit-photo', [$tenant->slug, $lead]) }}" target="_blank" rel="noopener" class="btn-primary" style="display: inline-block; padding: 0.5rem 1rem; text-decoration: none; font-size: 0.875rem;">View visit photo</a>
        </div>
    @endif
@endsection
