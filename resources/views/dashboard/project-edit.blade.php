@extends('layouts.app')

@section('title', 'Edit project')
@section('heading', 'Edit project')
@section('subtitle', $project->name)

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            <form method="POST" action="{{ route('tenant.projects.update', [$tenant->slug, $project]) }}">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 1rem;">
                    <label for="name" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Name *</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $project->name) }}" required maxlength="255" style="width: 100%; max-width: 320px; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('name')
                        <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                    @enderror
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="location" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Location</label>
                    <input id="location" type="text" name="location" value="{{ old('location', $project->location) }}" maxlength="255" style="width: 100%; max-width: 320px; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('location')
                        <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                    @enderror
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="status" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Status</label>
                    <select id="status" name="status" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                        <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $project->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div style="margin-bottom: 1rem;">
                    <label for="lock_days_override" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Lock days (override)</label>
                    <input id="lock_days_override" type="number" name="lock_days_override" value="{{ old('lock_days_override', $project->lock_days_override) }}" min="1" max="365" placeholder="Builder default" style="width: 100%; max-width: 10rem; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary);">Optional. Blank = use builder default ({{ $tenant->default_lock_days ?? 30 }} days).</p>
                    @error('lock_days_override')
                        <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <button type="submit" class="btn-primary">Save</button>
                    <a href="{{ route('tenant.projects.index', $tenant->slug) }}" style="margin-left: 0.75rem; font-size: 0.875rem;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
