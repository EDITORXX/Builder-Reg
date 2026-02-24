@extends('layouts.app')

@section('title', 'Profile')
@section('heading', 'Profile')
@section('subtitle', 'Your account details and settings')

@section('content')
    @if(session('success'))
        <p class="login-success" style="margin-bottom: 1rem;">{{ session('success') }}</p>
    @endif

    <div class="card profile-card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h2 class="card-title">My details</h2>
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
            @if($user->phone)
            <dl class="profile-row">
                <dt>Phone</dt>
                <dd>{{ $user->phone }}</dd>
            </dl>
            @endif
            <dl class="profile-row">
                <dt>Role</dt>
                <dd>{{ $user->getRoleLabel() }}</dd>
            </dl>
            @if($user->builderFirm ?? null)
            <dl class="profile-row">
                <dt>Builder firm</dt>
                <dd>{{ $user->builderFirm->name }}</dd>
            </dl>
            @endif
            @if($user->channelPartner ?? null)
            <dl class="profile-row">
                <dt>Firm name</dt>
                <dd>{{ $user->channelPartner->firm_name ?? '—' }}</dd>
            </dl>
            @if($user->channelPartner->rera_number ?? null)
            <dl class="profile-row">
                <dt>RERA number</dt>
                <dd>{{ $user->channelPartner->rera_number }}</dd>
            </dl>
            @endif
            @endif
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; max-width: 32rem;">
        <div class="card-header">
            <h2 class="card-title">Update details</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}" class="login-form">
                @csrf
                <div class="field">
                    <label for="name">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                    @error('phone')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Save changes</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; max-width: 32rem;">
        <div class="card-header">
            <h2 class="card-title">Reset password</h2>
            <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Change your login password.</p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password') }}" class="login-form">
                @csrf
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-primary">Update password</button>
            </form>
        </div>
    </div>
@endsection
