<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('tenant.settings.update', $tenant->slug) }}" enctype="multipart/form-data" style="max-width: 32rem;">
            @csrf
            @method('PUT')

            @if($tenant->getLogoUrl())
                <div style="margin-bottom: 1rem;">
                    <p style="font-size: 0.875rem; margin: 0 0 0.25rem 0;">Current logo</p>
                    <img src="{{ $tenant->getLogoUrl() }}" alt="Logo" style="width: 80px; height: 80px; object-fit: contain; border: 1px solid var(--border); border-radius: var(--radius);">
                </div>
            @endif

            <div style="margin-bottom: 1rem;">
                <label for="logo" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Logo (upload new)</label>
                <input id="logo" type="file" name="logo" accept="image/*" style="width: 100%; padding: 0.5rem 0; font-size: 0.875rem;">
                @error('logo')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="logo_url" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Logo URL (optional, overrides upload if set)</label>
                <input id="logo_url" type="text" name="logo_url" value="{{ old('logo_url', $tenant->getLogoUrl()) }}" maxlength="500" placeholder="https://..." style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                @error('logo_url')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="primary_color" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Primary colour</label>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <input id="primary_color_picker" type="color" value="{{ old('primary_color', $tenant->getPrimaryColor() ?? '#2563eb') }}" style="width: 3rem; height: 2.25rem; padding: 0; border: 1px solid var(--border); border-radius: var(--radius); cursor: pointer;" title="Pick colour">
                    <input id="primary_color" type="text" name="primary_color" value="{{ old('primary_color', $tenant->getPrimaryColor() ?? '#2563eb') }}" maxlength="50" placeholder="#2563eb" style="flex: 1; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                </div>
                <script>document.getElementById('primary_color_picker').addEventListener('input', function(){ document.getElementById('primary_color').value = this.value; }); document.getElementById('primary_color').addEventListener('input', function(v){ var c = document.getElementById('primary_color_picker'); if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) c.value = this.value; });</script>
                @error('primary_color')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>

            <div style="margin-bottom: 1rem;">
                <label for="default_lock_days" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Default lock days</label>
                <input id="default_lock_days" type="number" name="default_lock_days" value="{{ old('default_lock_days', $tenant->default_lock_days ?? 30) }}" min="1" max="365" style="width: 100%; max-width: 8rem; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary);">Visit confirm hone par customer kitne din ke liye lock rahega (1–365).</p>
                @error('default_lock_days')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>

            <h3 style="font-size: 1rem; margin: 1.25rem 0 0.75rem 0;">Mail setup</h3>
            <p style="margin: 0 0 0.75rem 0; font-size: 0.875rem; color: var(--text-secondary);">CP ko bhejne wale emails (approve/reject, new customer) is address se jayenge.</p>
            <div style="margin-bottom: 1rem;">
                <label for="mail_from_address" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Mail from address</label>
                <input id="mail_from_address" type="email" name="mail_from_address" value="{{ old('mail_from_address', $tenant->settings['mail_from_address'] ?? '') }}" placeholder="noreply@yourcompany.com" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                @error('mail_from_address')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="mail_from_name" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Mail from name</label>
                <input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name', $tenant->settings['mail_from_name'] ?? '') }}" maxlength="100" placeholder="e.g. Builder Name" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                @error('mail_from_name')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Save settings</button>
        </form>
    </div>
</div>
