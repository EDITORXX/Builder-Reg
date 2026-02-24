@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subtitle', 'Channel Partner')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">My overview</h2>
        </div>
        <div class="card-body">
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-label">My leads</div>
                    <div class="stat-value">{{ $leadsCount ?? 0 }}</div>
                </div>
            </div>
            <p style="margin: 1rem 0 0; font-size: 0.875rem;">
                <a href="{{ route('cp.leads') }}">View my leads</a> ·
                <a href="{{ route('cp.my-applications') }}">My applications</a>
            </p>
        </div>
    </div>
@endsection
