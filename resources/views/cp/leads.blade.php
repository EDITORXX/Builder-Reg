@extends('layouts.app')

@section('title', 'My Leads')
@section('heading', 'My Leads')
@section('subtitle', 'Leads assigned to you')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">My Leads</h2>
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
                                <th style="padding: 0.5rem 0;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leads as $lead)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ $lead->customer?->name ?? '—' }}<br><span style="font-size: 0.8125rem; color: var(--text-secondary);">{{ $lead->customer?->mobile ?? '' }}</span></td>
                                    <td style="padding: 0.5rem 0;">{{ $lead->project?->name ?? '—' }}</td>
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $lead->sales_status ?? '—') }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $lead->created_at?->format('M j, Y') ?? '—' }}</td>
                                    <td style="padding: 0.5rem 0;">
                                        @if($lead->project?->builderFirm?->slug)
                                            <a href="{{ route('tenant.leads.show', [$lead->project->builderFirm->slug, $lead]) }}" style="font-size: 0.875rem;">View</a>
                                        @else
                                            —
                                        @endif
                                    </td>
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
