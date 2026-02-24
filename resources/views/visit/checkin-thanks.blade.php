<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thank you</title>
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
                <h1 class="login-title">Thank you</h1>
                <p class="msg-success" style="margin: 0 0 1rem 0;">Your check-in has been submitted for manager verification.</p>
                <a href="{{ url('/') }}" class="btn-primary" style="display: inline-block; text-decoration: none;">Back to home</a>
            </div>
        </div>
    </div>
</body>
</html>
