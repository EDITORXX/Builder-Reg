<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
        <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Your account and builder details.</p>
    </div>
    <div class="card-body">
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
            @if($user->avatar_url ?? null)
                <img src="{{ $user->avatar_url }}" alt="Profile" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border);">
            @else
                <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--tenant-primary, #0f766e); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 600;">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</div>
            @endif
            <div>
                <strong>{{ $user->name ?? '—' }}</strong>
                <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--text-secondary);">{{ $user->getRoleLabel() }}</p>
            </div>
        </div>
        <dl class="profile-row">
            <dt>Name</dt>
            <dd>{{ $user->name ?? '—' }}</dd>
        </dl>
        <dl class="profile-row">
            <dt>Email</dt>
            <dd>{{ $user->email ?? '—' }}</dd>
        </dl>
        <dl class="profile-row">
            <dt>Role</dt>
            <dd>{{ $user->getRoleLabel() }}</dd>
        </dl>
        @if(isset($tenant) && $tenant)
        <dl class="profile-row">
            <dt>Builder firm</dt>
            <dd>{{ $tenant->name }}</dd>
        </dl>
        @elseif($user->builderFirm ?? null)
        <dl class="profile-row">
            <dt>Builder firm</dt>
            <dd>{{ $user->builderFirm->name }}</dd>
        </dl>
        @endif
        <p style="margin: 1rem 0 0; font-size: 0.875rem;">
            <a href="{{ route('profile.show') }}">Edit profile & picture</a>
        </p>
    </div>
</div>
