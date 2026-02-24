@extends('layouts.app')

@section('title', 'Scheduled Visits')
@section('heading', 'Scheduled Visits')
@section('subtitle', 'Schedule visits and share QR for check-in')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <h2 class="card-title">Scheduled visits</h2>
            @if($buildersWithFeature->isNotEmpty())
                <a href="{{ route('cp.scheduled-visits.create') }}" class="btn-primary" style="text-decoration: none;">Schedule visit</a>
            @endif
        </div>
        <div class="card-body">
            @if(session('success'))
                <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
            @endif

            @if($buildersWithFeature->isEmpty())
                <p style="margin: 0; color: var(--text-secondary);">Scheduled visit (QR) feature is not enabled for any of your builders. Contact your builder admin.</p>
                <p style="margin: 0.75rem 0 0;"><a href="{{ route('cp.direct-visit') }}">Register a direct visit</a> (no QR) instead.</p>
            @elseif($schedules->isEmpty())
                <p style="margin: 0; color: var(--text-secondary);">No scheduled visits yet.</p>
                <p style="margin: 0.75rem 0 0;"><a href="{{ route('cp.scheduled-visits.create') }}">Schedule a visit</a> or <a href="{{ route('cp.direct-visit') }}">register direct visit</a>.</p>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Builder / Project</th>
                                <th style="padding: 0.5rem 0;">Customer</th>
                                <th style="padding: 0.5rem 0;">Scheduled at</th>
                                <th style="padding: 0.5rem 0;">Status</th>
                                <th style="padding: 0.5rem 0;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedules as $s)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ $s->builderFirm?->name ?? '—' }}<br><span style="font-size: 0.8125rem; color: var(--text-secondary);">{{ $s->project?->name ?? '—' }}</span></td>
                                    <td style="padding: 0.5rem 0;">{{ $s->customer_name }}<br><span style="font-size: 0.8125rem;">{{ $s->customer_mobile }}</span></td>
                                    <td style="padding: 0.5rem 0;">{{ $s->scheduled_at?->format('M j, Y H:i') ?? '—' }}</td>
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $s->status ?? '—') }}</td>
                                    <td style="padding: 0.5rem 0;">
                                        <a href="{{ route('cp.scheduled-visits.show', $s) }}" style="font-size: 0.875rem;">View / QR</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($schedules->hasPages())
                    <div style="margin-top: 1rem;">{{ $schedules->links() }}</div>
                @endif
            @endif
        </div>
    </div>
    <p style="font-size: 0.875rem;"><a href="{{ route('cp.direct-visit') }}">Register direct visit</a> (on-site, no scheduling)</p>
@endsection
