<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in — {{ $builder->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="login-container register-wide">
            <div class="login-card register-card">
                <div class="login-logo">
                    <span class="login-logo-icon">BP</span>
                    <span class="login-logo-text">{{ $builder->name }}</span>
                </div>
                <h1 class="login-title">Visit Check-in</h1>
                <p class="login-subtitle">Your details are pre-filled. Please upload your photo and submit.</p>

                @if(session('success'))
                    <p class="msg-success">{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p style="margin: 0 0 1rem 0; padding: 0.75rem 1rem; background: rgb(220 38 38 / 0.08); border-radius: var(--radius); font-size: 0.9375rem; color: var(--error);">{{ session('error') }}</p>
                @endif

                <div class="register-section" style="margin-bottom: 1rem;">
                    <h2 class="register-section-title">Your details</h2>
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;"><strong>Project:</strong> {{ $schedule->project->name ?? '—' }}</p>
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;"><strong>Name:</strong> {{ $schedule->customer_name }}</p>
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;"><strong>Mobile:</strong> {{ $schedule->customer_mobile }}</p>
                    @if($schedule->customer_email)
                        <p style="margin: 0 0 0.5rem 0; font-size: 0.875rem;"><strong>Email:</strong> {{ $schedule->customer_email }}</p>
                    @endif
                </div>

                <form method="POST" action="{{ route('visit.checkin.submit', $schedule->token) }}" class="login-form" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label for="visit_photo">Photo <span aria-hidden="true">*</span></label>
                        <input id="visit_photo" type="file" name="visit_photo" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                        <p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--text-secondary);">Upload a clear photo (required for verification).</p>
                        @error('visit_photo')<p class="login-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="login-actions" style="margin-top: 1.5rem;">
                        <button type="submit" class="btn-primary">Submit for Manager Verification</button>
                        <a href="{{ url('/') }}" class="login-back">← Back to home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
