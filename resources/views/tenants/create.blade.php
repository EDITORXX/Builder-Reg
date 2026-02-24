@extends('layouts.app')

@section('title', 'New tenant')
@section('heading', 'New tenant')
@section('subtitle', 'Create a builder tenant and its first admin user. Tenant URL will be based on company name.')

@section('content')
    <div class="card" style="max-width: 32rem;">
        <div class="card-header">
            <h2 class="card-title">Create tenant</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.store') }}" enctype="multipart/form-data" class="login-form">
                @csrf
                <div class="field">
                    <label for="name">Company name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Acme Builders Pvt Ltd">
                    @error('name')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="plan_id">Plan</label>
                    <select id="plan_id" name="plan_id" required style="width: 100%; padding: 0.625rem 0.875rem; font-size: 0.9375rem; border: 1px solid var(--border); border-radius: var(--radius); font-family: inherit;">
                        <option value="">Select plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} — {{ $plan->max_users }} users, {{ $plan->max_projects }} project(s), {{ $plan->max_channel_partners }} brokers, {{ $plan->max_leads }} leads
                            </option>
                        @endforeach
                    </select>
                    @error('plan_id')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="email">Admin email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="admin@company.com">
                    @error('email')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">Admin password</label>
                    <input id="password" type="password" name="password" required placeholder="••••••••">
                    @error('password')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="••••••••">
                </div>
                <div class="field">
                    <label for="logo_url">Logo URL (optional)</label>
                    <input id="logo_url" type="text" name="logo_url" value="{{ old('logo_url') }}" placeholder="https://...">
                    @error('logo_url')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="logo">Or upload logo (optional)</label>
                    <input id="logo" type="file" name="logo" accept="image/*">
                    @error('logo')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="primary_color">Primary colour (optional)</label>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <input id="primary_color_picker" type="color" value="{{ old('primary_color', '#2563eb') }}" style="width: 3rem; height: 2rem; padding: 0; border: 1px solid var(--border); border-radius: var(--radius); cursor: pointer;">
                        <input type="text" id="primary_color" name="primary_color" value="{{ old('primary_color', '#2563eb') }}" placeholder="#2563eb" style="width: 6rem; padding: 0.375rem 0.5rem; font-size: 0.875rem;">
                    </div>
                    @error('primary_color')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="login-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">Create tenant</button>
                    <a href="{{ route('tenants.index') }}" class="login-back">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        (function() {
            var picker = document.getElementById('primary_color_picker');
            var hex = document.getElementById('primary_color');
            if (picker && hex) {
                picker.addEventListener('input', function() { hex.value = this.value; });
                hex.addEventListener('input', function() {
                    if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) picker.value = this.value;
                });
            }
        })();
    </script>
@endsection
