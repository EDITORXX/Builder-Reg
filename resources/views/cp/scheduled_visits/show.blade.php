@extends('layouts.app')

@section('title', 'Visit schedule')
@section('heading', 'Visit schedule')
@section('subtitle', $schedule->customer_name . ' — ' . ($schedule->project?->name ?? ''))

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            @if(session('success'))
                <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
            @endif
            <p style="margin: 0 0 0.5rem 0;"><strong>Customer:</strong> {{ $schedule->customer_name }} · {{ $schedule->customer_mobile }}</p>
            <p style="margin: 0 0 0.5rem 0;"><strong>Project:</strong> {{ $schedule->project?->name ?? '—' }}</p>
            <p style="margin: 0 0 0.5rem 0;"><strong>Scheduled:</strong> {{ $schedule->scheduled_at?->format('M j, Y H:i') ?? '—' }}</p>
            <p style="margin: 0 0 1rem 0;"><strong>Status:</strong> {{ str_replace('_', ' ', $schedule->status) }}</p>
            @if($schedule->status === \App\Models\VisitSchedule::STATUS_SCHEDULED && !$schedule->isTokenExpired())
                <div style="margin: 1.5rem 0;">
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">Share this QR or link with the customer for check-in:</p>
                    <img src="data:image/png;base64,{{ $qrPngBase64 }}" alt="QR code" style="display: block; width: 280px; height: 280px; border: 1px solid var(--border); border-radius: var(--radius);">
                    <p style="margin: 0.5rem 0 0; font-size: 0.8125rem; word-break: break-all; color: var(--text-secondary);">{{ $checkInUrl }}</p>
                    <a href="{{ route('cp.scheduled-visits.show', ['visitSchedule' => $schedule, 'download' => 1]) }}" class="btn-primary" style="display: inline-block; margin-top: 0.75rem; text-decoration: none;">Download QR</a>
                </div>
            @endif
            <a href="{{ route('cp.scheduled-visits.index') }}" class="login-back">← Back to list</a>
        </div>
    </div>
@endsection
