<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    <span class="login-logo-icon">BP</span>
                    <span class="login-logo-text">Builder Partner</span>
                </div>
                <h1 class="login-title">Sign in</h1>
                <p class="login-subtitle">Enter your credentials to access the platform.</p>
                @if(session('success'))
                    <p class="msg-success">{{ session('success') }}</p>
                @endif
                <form method="POST" action="{{ route('login') }}" class="login-form">
                    @csrf
                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="you@example.com" autocomplete="email">
                        @error('email')
                            <p class="login-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            placeholder="••••••••" autocomplete="current-password">
                        @error('password')
                            <p class="login-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="login-actions">
                        <button type="submit" class="btn-primary">Sign in</button>
                        <a href="{{ url('/') }}" class="login-back">← Back to home</a>
                    </div>
                </form>

                <div class="login-demo">
                    <strong>Demo accounts</strong><br>
                    Super Admin: <code>super@builder.com</code> / <code>password</code><br>
                    Builder Admin: <code>admin@builder.com</code> / <code>password</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
