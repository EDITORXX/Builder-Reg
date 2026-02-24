@extends('layouts.app')

@section('title', 'Schedule Visit')
@section('heading', 'Schedule Visit')
@section('subtitle', 'Create a scheduled visit and share QR with customer')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem; max-width: 28rem;">
        <div class="card-header">
            <h2 class="card-title">New scheduled visit</h2>
        </div>
        <div class="card-body">
            @if(session('error'))
                <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('cp.scheduled-visits.store') }}" class="login-form">
                @csrf
                @if($buildersWithFeature->count() > 1)
                    <div class="field">
                        <label for="builder_firm_id">Builder *</label>
                        <select id="builder_firm_id" name="builder_firm_id" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);" onchange="window.location.href='{{ route('cp.scheduled-visits.create') }}?builder_id='+this.value">
                            <option value="">Select builder</option>
                            @foreach($buildersWithFeature as $b)
                                <option value="{{ $b->id }}" {{ (old('builder_firm_id', $selectedBuilder?->id) == $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                        @error('builder_firm_id')<p class="login-error">{{ $message }}</p>@enderror
                    </div>
                @else
                    <input type="hidden" name="builder_firm_id" value="{{ $selectedBuilder?->id }}">
                @endif

                <div class="field">
                    <label for="project_id">Project *</label>
                    <select id="project_id" name="project_id" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                        <option value="">Select project</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="customer_name">Customer name *</label>
                    <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required maxlength="255" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('customer_name')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="customer_mobile">Customer mobile *</label>
                    <input id="customer_mobile" type="text" name="customer_mobile" value="{{ old('customer_mobile') }}" required maxlength="20" placeholder="10-digit number" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('customer_mobile')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="customer_email">Customer email</label>
                    <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}" maxlength="255" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('customer_email')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="scheduled_at">Visit date & time *</label>
                    <input id="scheduled_at" type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('scheduled_at')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="login-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">Create & get QR</button>
                    <a href="{{ route('cp.scheduled-visits.index') }}" class="login-back">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
