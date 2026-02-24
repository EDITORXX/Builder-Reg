@extends('layouts.app')

@section('title', 'Edit tenant')
@section('heading', 'Edit tenant')
@section('subtitle', 'Change plan for ' . $tenant->name)

@section('content')
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

    <div class="card" style="max-width: 32rem;">
        <div class="card-header">
            <h2 class="card-title">{{ $tenant->name }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="login-form">
                @csrf
                @method('PUT')

                <h3 style="font-size: 1rem; margin: 0 0 0.75rem 0;">Plan & limits</h3>
                <div class="field" style="margin-bottom: 1rem;">
                    <label for="plan_id" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Plan</label>
                    <select id="plan_id" name="plan_id" required style="width: 100%; padding: 0.625rem 0.875rem; font-size: 0.9375rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit;">
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ (old('plan_id', $tenant->plan_id) == $plan->id) ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ $plan->max_users }} users, {{ $plan->max_projects }} project(s), {{ $plan->max_channel_partners }} brokers, {{ $plan->max_leads }} leads
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>

                <h3 style="font-size: 1rem; margin: 1.25rem 0 0.75rem 0;">Scheduled visit (QR)</h3>
                <p style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: var(--text-secondary);">CP can schedule visits and generate QR for check-in. Enable and set limit for this tenant.</p>
                <div class="field" style="margin-bottom: 1rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                        <input type="hidden" name="scheduled_visit_enabled" value="0">
                        <input type="checkbox" name="scheduled_visit_enabled" value="1" {{ old('scheduled_visit_enabled', $tenant->scheduled_visit_enabled ?? false) ? 'checked' : '' }}>
                        <span>Scheduled visit (QR) feature enabled</span>
                    </label>
                </div>
                <div class="field" style="margin-bottom: 1rem;">
                    <label for="scheduled_visit_limit" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Max QR / scheduled visits for this tenant</label>
                    <input id="scheduled_visit_limit" type="number" name="scheduled_visit_limit" value="{{ old('scheduled_visit_limit', $tenant->scheduled_visit_limit ?? '') }}" min="0" placeholder="Unlimited" style="width: 100%; max-width: 10rem; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary);">Is tenant ke liye CPs kitne scheduled visits (QR) ek saath rakh sakte hain. Khali = unlimited.@if(isset($scheduled_visit_used)) (Currently {{ $scheduled_visit_used }} in use.)@endif</p>
                    @error('scheduled_visit_limit')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="login-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">Update plan</button>
                    <a href="{{ route('tenants.index') }}" class="login-back">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
