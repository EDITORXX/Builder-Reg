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

        @if(empty($managerProjectsViewOnly))
        <form method="POST" action="{{ route('tenant.projects.store', $tenant->slug) }}" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;">
            @csrf
            <div style="min-width: 180px;">
                <label for="project_name" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Name *</label>
                <input id="project_name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                @error('name')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>
            <div style="min-width: 180px;">
                <label for="project_location" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Location</label>
                <input id="project_location" type="text" name="location" value="{{ old('location') }}" maxlength="255" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                @error('location')
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn-primary">Add project</button>
        </form>
        @endif

        @if($projects->isEmpty())
            <p style="margin: 0; color: var(--text-secondary);">No projects yet.@if(empty($managerProjectsViewOnly)) Add one above.@endif</p>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                            <th style="padding: 0.5rem 0;">Name</th>
                            <th style="padding: 0.5rem 0;">Location</th>
                            <th style="padding: 0.5rem 0;">Status</th>
                            <th style="padding: 0.5rem 0;">Lock days</th>
                            @if(empty($managerProjectsViewOnly))
                            <th style="padding: 0.5rem 0;">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            @php
                                $lockDays = $project->lock_days_override ?? ($tenant->default_lock_days ?? 30);
                                $lockDaysLabel = $lockDays . ' days' . ($project->lock_days_override ? '' : ' (default)');
                            @endphp
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="padding: 0.5rem 0;">{{ $project->name }}</td>
                                <td style="padding: 0.5rem 0;">{{ $project->location ?? '—' }}</td>
                                <td style="padding: 0.5rem 0;">
                                    <span style="font-size: 0.8125rem;">{{ $project->status }}</span>
                                </td>
                                <td style="padding: 0.5rem 0;">{{ $lockDaysLabel }}</td>
                                @if(empty($managerProjectsViewOnly))
                                <td style="padding: 0.5rem 0;">
                                    <a href="{{ route('tenant.projects.edit', [$tenant->slug, $project]) }}" style="font-size: 0.8125rem; margin-right: 0.5rem;">Edit</a>
                                    <form method="POST" action="{{ route('tenant.projects.update', [$tenant->slug, $project]) }}" style="display: inline-block; margin-right: 0.5rem;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="name" value="{{ $project->name }}">
                                        <input type="hidden" name="location" value="{{ $project->location }}">
                                        <input type="hidden" name="status" value="{{ $project->status === 'active' ? 'inactive' : 'active' }}">
                                        <button type="submit" style="font-size: 0.8125rem; background: none; border: none; cursor: pointer; color: var(--accent);">{{ $project->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('tenant.projects.destroy', [$tenant->slug, $project]) }}" style="display: inline-block;" onsubmit="return confirm('Delete this project?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="font-size: 0.8125rem; background: none; border: none; cursor: pointer; color: var(--error);">Delete</button>
                                    </form>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
