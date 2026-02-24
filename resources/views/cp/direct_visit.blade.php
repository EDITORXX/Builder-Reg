@extends('layouts.app')

@section('title', 'Register Direct Visit')
@section('heading', 'Register Direct Visit')
@section('subtitle', 'On-site registration with photo')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem; max-width: 28rem;">
        <div class="card-header">
            <h2 class="card-title">Direct visit registration</h2>
        </div>
        <div class="card-body">
            @if(session('success'))
                <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
            @endif
            @if(session('error'))
                <p style="margin: 0 0 1rem 0; color: var(--error);">{{ session('error') }}</p>
            @endif

            <form method="POST" action="{{ route('cp.direct-visit.submit') }}" enctype="multipart/form-data" class="login-form">
                @csrf
                @if($builders->count() > 1)
                    <div class="field">
                        <label for="builder_firm_id">Builder *</label>
                        <select id="builder_firm_id" name="builder_firm_id" required style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);" onchange="window.location.href='{{ route('cp.direct-visit') }}?builder_id='+this.value">
                            <option value="">Select builder</option>
                            @foreach($builders as $b)
                                <option value="{{ $b->id }}" {{ (old('builder_firm_id', $selectedBuilder?->id) == $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
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
                    <input id="customer_mobile" type="text" name="customer_mobile" value="{{ old('customer_mobile') }}" required maxlength="20" style="width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                    @error('customer_mobile')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="visit_photo">Photo *</label>
                    <input id="visit_photo" type="file" name="visit_photo" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                    <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary);">Required for verification.</p>
                    @error('visit_photo')<p class="login-error">{{ $message }}</p>@enderror
                </div>
                <div class="login-actions" style="margin-top: 1.25rem;">
                    <button type="submit" class="btn-primary">Submit for verification</button>
                    <a href="{{ route('cp.dashboard') }}" class="login-back">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <p style="font-size: 0.875rem;"><a href="{{ route('cp.scheduled-visits.index') }}">Scheduled visits</a></p>
@endsection
