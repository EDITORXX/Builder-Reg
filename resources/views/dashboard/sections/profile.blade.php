<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
        <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Your account and builder details.</p>
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
        <dl class="profile-row">
            <dt>Role</dt>
            <dd>{{ str_replace('_', ' ', ucfirst($user->role ?? '—')) }}</dd>
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
    </div>
</div>
