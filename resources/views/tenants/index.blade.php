@extends('layouts.app')

@section('title', 'Tenants')
@section('heading', 'Tenants')
@section('subtitle', 'All builder tenants. Open a tenant dashboard via its URL.')

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

    @if(session('show_password') && session('password_value'))
        <div class="card password-reveal-card" style="margin-bottom: 1rem; border-color: var(--accent); background: #eff6ff;">
            <div class="card-body">
                <p style="margin: 0 0 0.5rem 0; font-weight: 600;">New password (copy now — won’t be shown again)</p>
                <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem; color: var(--text-secondary);">Tenant: {{ session('password_tenant') }} — {{ session('password_email') }}</p>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <code id="new-password-value" style="padding: 0.5rem 0.75rem; background: #fff; border: 1px solid var(--border); border-radius: var(--radius); font-size: 1rem;">{{ session('password_value') }}</code>
                    <button type="button" onclick="copyNewPassword()" class="btn-primary" style="cursor: pointer;">Copy</button>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($tenants->isEmpty())
                <p style="color: var(--text-secondary);">No tenants yet. <a href="{{ route('tenants.create') }}">Create the first tenant</a>.</p>
            @else
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.75rem 0; font-weight: 600;">Name</th>
                                <th style="padding: 0.75rem 0; font-weight: 600;">Plan</th>
                                <th style="padding: 0.75rem 0; font-weight: 600;">Admin email</th>
                                <th style="padding: 0.75rem 0; font-weight: 600;">Password</th>
                                <th style="padding: 0.75rem 0; font-weight: 600;">URL</th>
                                <th style="padding: 0.75rem 0; font-weight: 600;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $t)
                                @php $admin = $t->users->firstWhere('role', 'builder_admin'); @endphp
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.75rem 0;">{{ $t->name }}</td>
                                    <td style="padding: 0.75rem 0;">
                                        {{ $t->plan?->name ?? '—' }}
                                        <a href="{{ route('tenants.edit', $t) }}" style="margin-left: 0.25rem; font-size: 0.75rem;">Change</a>
                                    </td>
                                    <td style="padding: 0.75rem 0;">{{ $admin?->email ?? '—' }}</td>
                                    <td style="padding: 0.75rem 0;">
                                        @if($admin)
                                            <span class="password-cell">
                                                <span class="password-mask">••••••••</span>
                                                <button type="button" class="password-eye-btn" onclick="togglePasswordHint(this)" title="Password is encrypted. Click Reset to generate a new password and see it once." aria-label="Show password hint">
                                                    <svg class="eye-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                </button>
                                                <form method="POST" action="{{ route('tenants.reset-password', $t) }}" style="display: inline;" onsubmit="return confirm('Generate a new password? The current password will stop working. You will see the new password once.');">
                                                    @csrf
                                                    <button type="submit" class="password-reset-btn">Reset</button>
                                                </form>
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="padding: 0.75rem 0;">
                                        <a href="{{ url("/t/{$t->slug}") }}" target="_blank" style="font-size: 0.8125rem;">{{ url("/t/{$t->slug}") }}</a>
                                    </td>
                                    <td style="padding: 0.75rem 0;">
                                        <a href="{{ route('tenants.edit', $t) }}" style="margin-right: 0.5rem; font-size: 0.875rem;">Edit plan</a>
                                        <a href="{{ url("/t/{$t->slug}") }}" class="btn-primary" style="display: inline-block; padding: 0.375rem 0.75rem; font-size: 0.875rem; text-decoration: none;">Open dashboard</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <style>
        .password-cell { display: inline-flex; align-items: center; gap: 0.35rem; }
        .password-mask { font-family: monospace; letter-spacing: 0.1em; color: var(--text-secondary); }
        .password-eye-btn, .password-reset-btn { background: none; border: none; cursor: pointer; padding: 0.2rem; color: var(--text-secondary); display: inline-flex; align-items: center; }
        .password-eye-btn:hover, .password-reset-btn:hover { color: var(--accent); }
        .password-reset-btn { font-size: 0.8125rem; text-decoration: underline; }
    </style>
    <script>
        function togglePasswordHint(btn) {
            var msg = 'Password is encrypted and cannot be viewed. Click "Reset" to generate a new password and see it once.';
            if (btn.getAttribute('data-tooltip')) {
                return;
            }
            btn.setAttribute('data-tooltip', '1');
            alert(msg);
        }
        function copyNewPassword() {
            var el = document.getElementById('new-password-value');
            if (!el) return;
            navigator.clipboard.writeText(el.textContent).then(function() {
                var btn = document.querySelector('.password-reveal-card button');
                if (btn) { btn.textContent = 'Copied!'; setTimeout(function() { btn.textContent = 'Copy'; }, 1500); }
            });
        }
    </script>
@endsection
