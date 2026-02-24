<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Create manager</h2>
        <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Managers can view leads, CPs, locks, visits, reports and send OTP for visits. Create a new manager for this builder.</p>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p class="msg-success">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
        @endif
        <form method="POST" action="{{ route('tenant.managers.store', $tenant->slug) }}" style="max-width: 400px;">
            @csrf
            <div class="field" style="margin-bottom: 1rem;">
                <label for="name" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                @error('name')
                    <p class="login-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="field" style="margin-bottom: 1rem;">
                <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                @error('email')
                    <p class="login-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="field" style="margin-bottom: 1rem;">
                <label for="password" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Password</label>
                <input id="password" type="password" name="password" required minlength="8"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                @error('password')
                    <p class="login-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="field" style="margin-bottom: 1rem;">
                <label for="password_confirmation" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8"
                    style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
            </div>
            <button type="submit" class="btn-primary">Create manager</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Managers</h2>
    </div>
    <div class="card-body">
        @if(isset($managers) && $managers->isNotEmpty())
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Name</th>
                            <th style="padding: 0.5rem 0;">Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($managers as $m)
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $m->name }}</td>
                                <td style="padding: 0.5rem 0;">{{ $m->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="margin: 0; color: var(--text-secondary);">No managers yet. Create one above.</p>
        @endif
    </div>
</div>
