<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @php
        $bgImage = env('LOGIN_BG_IMAGE', '');
    @endphp
    <style>
        .login-panel-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #e8f4f4 0%, #d4e8e8 50%, #c2ddd8 100%);
            background-size: cover;
            background-position: center;
            @if($bgImage)
            background-image: url('{{ $bgImage }}');
            @endif
        }
        .login-panel {
            width: 100%;
            max-width: 380px;
            background: #2d5f5f;
            border-radius: 1.25rem;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
            color: #f0f7f7;
        }
        .login-panel-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255,255,255,0.9);
        }
        .login-panel-icon svg { width: 40px; height: 40px; }
        .login-panel-title { font-size: 1.5rem; font-weight: 600; margin: 0 0 0.5rem 0; text-align: center; color: #fff; }
        .login-panel-sub { font-size: 0.875rem; opacity: 0.9; margin: 0 0 1.75rem 0; text-align: center; }
        .login-panel .line-field { margin-bottom: 1.5rem; }
        .login-panel .line-field input {
            width: 100%;
            padding: 0.5rem 0 0.75rem 0;
            font-size: 0.9375rem;
            color: #fff;
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255,255,255,0.5);
            border-radius: 0;
            font-family: inherit;
            display: block;
        }
        .login-panel .line-field input::placeholder { color: rgba(255,255,255,0.6); }
        .login-panel .line-field input:focus {
            outline: none;
            border-bottom-color: rgba(255,255,255,0.9);
        }
        .login-panel .line-field label {
            display: block;
            font-size: 0.8125rem;
            color: rgba(255,255,255,0.75);
            margin-bottom: 0.25rem;
        }
        .login-panel .btn-login {
            width: 100%;
            padding: 0.875rem 1.5rem;
            margin-top: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: #2d5f5f;
            background: #fff;
            border: none;
            border-radius: 9999px;
            cursor: pointer;
            font-family: inherit;
            transition: opacity 0.2s, transform 0.1s;
        }
        .login-panel .btn-login:hover { opacity: 0.95; }
        .login-panel .btn-login:active { transform: scale(0.99); }
        .login-panel-footer { margin-top: 1.5rem; text-align: center; font-size: 0.8125rem; color: rgba(255,255,255,0.75); }
        .login-panel-footer a { color: #8fdfc7; text-decoration: none; }
        .login-panel-footer a:hover { text-decoration: underline; }
        .login-panel .msg-success { font-size: 0.875rem; color: #8fdfc7; margin-bottom: 1rem; padding: 0.5rem 0; }
        .login-panel .login-error { font-size: 0.8125rem; color: #fecaca; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="login-panel-page">
        <div class="login-panel">
            <div class="login-panel-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <h1 class="login-panel-title">Log in</h1>
            <p class="login-panel-sub">Enter your email and password to continue.</p>
            @if(session('success'))
                <p class="msg-success">{{ session('success') }}</p>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="line-field">
                    <label for="email">E-mail address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="you@example.com" autocomplete="email">
                    @error('email')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <div class="line-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" required
                        placeholder="••••••••" autocomplete="current-password">
                    @error('password')
                        <p class="login-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn-login">Log in</button>
            </form>
            <p class="login-panel-footer">
                <a href="{{ url('/') }}">← Back to home</a>
            </p>
            <p class="login-panel-footer" style="margin-top: 0.5rem; font-size: 0.75rem; opacity: 0.8;">
                Demo: super@builder.com / password
            </p>
        </div>
    </div>
</body>
</html>
