@extends('layouts.app')

@section('title', 'Edit manager')
@section('heading', 'Edit manager')
@section('subtitle', 'Update manager name and email')

@section('content')
    <div style="margin-bottom: 1rem; font-size: 0.875rem;">
        <a href="{{ route('tenant.managers.index', $tenant->slug) }}">Managers</a>
        <span style="color: var(--text-secondary);"> / </span>
        <span>{{ $manager->name }}</span>
    </div>

    <div class="card" style="max-width: 400px;">
        <div class="card-header">
            <h2 class="card-title">Profile</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenant.managers.update', [$tenant->slug, $manager]) }}">
                @csrf
                @method('PUT')
                <div class="field" style="margin-bottom: 1rem;">
                    <label for="name" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $manager->name) }}" required
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                    @error('name')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="field" style="margin-bottom: 1rem;">
                    <label for="email" style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem;">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $manager->email) }}" required
                        style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                    @error('email')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-primary">Save</button>
            </form>
        </div>
    </div>
@endsection
