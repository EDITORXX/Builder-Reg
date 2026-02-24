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
                    <div class="stat-label">Site visits (visit done)</div>
                    <div class="stat-value" style="font-size: 1.25rem;">{{ $visit_done_count }}</div>
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
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $lead->status ?? '—') }}</td>
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
@endsection
