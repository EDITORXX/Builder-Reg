@extends('layouts.app')

@section('title', 'Edit tenant')
@section('heading', 'Edit tenant')
@section('subtitle', 'Change plan for ' . $tenant->name)

@section('content')
    <div class="card" style="max-width: 32rem;">
        <div class="card-header">
            <h2 class="card-title">{{ $tenant->name }}</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.update', $tenant) }}" class="login-form">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="plan_id">Plan</label>
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
                <div class="login-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">Update plan</button>
                    <a href="{{ route('tenants.index') }}" class="login-back">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
