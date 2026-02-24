<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">{{ $sectionLabel }}</h2>
        <p class="card-subtitle" style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">Build CP Registration and Customer Registration forms. Set one form per type as active for public links.</p>
    </div>
    <div class="card-body">
        @if(session('success'))
            <p style="margin: 0 0 1rem 0; color: var(--success); font-size: 0.875rem;">{{ session('success') }}</p>
        @endif
        @if(session('error'))
            <p style="margin: 0 0 1rem 0; color: var(--error); font-size: 0.875rem;">{{ session('error') }}</p>
        @endif

        <div class="forms-create-block">
            <span class="forms-create-label">Create from template</span>
            <div class="forms-create-buttons">
                <form method="POST" action="{{ route('tenant.forms.from-template', $tenant->slug) }}" style="display: inline-block;">
                    @csrf
                    <input type="hidden" name="type" value="cp_registration">
                    <button type="submit" class="btn-primary">CP Registration</button>
                </form>
                <form method="POST" action="{{ route('tenant.forms.from-template', $tenant->slug) }}" style="display: inline-block;">
                    @csrf
                    <input type="hidden" name="type" value="customer_registration">
                    <button type="submit" class="btn-primary">Customer Registration</button>
                </form>
            </div>
            <a href="{{ route('tenant.forms.create', $tenant->slug) }}" class="forms-create-blank">Create new form (blank)</a>
        </div>

        @if($forms->isEmpty())
            <p style="margin: 0; color: var(--text-secondary); font-size: 0.875rem;">No forms yet. Use a template above or create a new form.</p>
        @else
            <div class="forms-table-section">
                <h3 class="forms-table-section-title">Your forms</h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Name</th>
                                <th style="padding: 0.5rem 0;">Type</th>
                                <th style="padding: 0.5rem 0;">Active</th>
                                <th style="padding: 0.5rem 0;">Public link</th>
                                <th style="padding: 0.5rem 0;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $f)
                                @php
                                    $publicUrl = $f->type === 'cp_registration'
                                        ? route('register.cp', $tenant->slug)
                                        : route('register.customer', $tenant->slug);
                                @endphp
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0; font-weight: 500;">{{ $f->name }}</td>
                                    <td style="padding: 0.5rem 0;">{{ str_replace('_', ' ', $f->type) }}</td>
                                    <td style="padding: 0.5rem 0;">
                                        @if($f->is_active)
                                            <span class="badge-active">Yes</span>
                                        @else
                                            <span class="badge-inactive">No</span>
                                        @endif
                                    </td>
                                    <td style="padding: 0.5rem 0;">
                                        <div class="form-link-cell">
                                            <span class="form-link-url">{{ $publicUrl }}</span>
                                            <div class="form-link-actions">
                                                <button type="button" class="btn-outline form-copy-btn" data-url="{{ e($publicUrl) }}">Copy</button>
                                                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="btn-outline">Open</a>
                                            </div>
                                            @if(!$f->is_active)
                                                <p class="form-link-hint">Set as active for this link to show this form.</p>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="padding: 0.5rem 0;">
                                        <div class="form-actions">
                                            <a href="{{ route('tenant.forms.edit', [$tenant->slug, $f]) }}" class="form-action-link">Edit</a>
                                            @if(!$f->is_active)
                                                <form method="POST" action="{{ route('tenant.forms.activate', [$tenant->slug, $f]) }}" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="form-action-btn set-active">Set active</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('tenant.forms.destroy', [$tenant->slug, $f]) }}" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this form? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="form-action-btn delete">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.querySelectorAll('.form-copy-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.getAttribute('data-url');
        if (!url) return;
        navigator.clipboard.writeText(url).then(function() {
            var t = btn.textContent;
            btn.textContent = 'Copied!';
            btn.classList.add('copied');
            setTimeout(function() {
                btn.textContent = t;
                btn.classList.remove('copied');
            }, 2000);
        });
    });
});
</script>
