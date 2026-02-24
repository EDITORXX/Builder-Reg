<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Install — Builder Platform</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-page">
        <div class="login-container" style="max-width: 520px;">
            <div class="login-card">
                <div class="login-logo">
                    <span class="login-logo-icon">BP</span>
                    <span class="login-logo-text">Builder Platform</span>
                </div>
                <h1 class="login-title">Installation</h1>
                <p class="login-subtitle">Enter your app and server details. After this, you can log in as Super Admin.</p>

                <form method="POST" action="{{ url('/install') }}" class="login-form">
                    @csrf

                    <div class="register-section">
                        <h2 class="register-section-title">App</h2>
                        <div class="field">
                            <label for="app_name">App name</label>
                            <input id="app_name" type="text" name="app_name" value="{{ old('app_name', 'Builder Platform') }}" required maxlength="100" placeholder="Builder Platform">
                            @error('app_name')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="app_url">App URL</label>
                            <input id="app_url" type="url" name="app_url" value="{{ old('app_url', request()->getSchemeAndHttpHost()) }}" required maxlength="255" placeholder="https://app.yourdomain.com">
                            @error('app_url')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="register-section">
                        <h2 class="register-section-title">Database</h2>
                        <div class="field">
                            <label for="db_host">DB host</label>
                            <input id="db_host" type="text" name="db_host" value="{{ old('db_host', '127.0.0.1') }}" required maxlength="255" placeholder="127.0.0.1">
                            @error('db_host')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="db_port">DB port</label>
                            <input id="db_port" type="text" name="db_port" value="{{ old('db_port', '3306') }}" maxlength="10" placeholder="3306">
                            @error('db_port')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="db_database">DB name</label>
                            <input id="db_database" type="text" name="db_database" value="{{ old('db_database') }}" required maxlength="255" placeholder="laravel">
                            @error('db_database')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="db_username">DB username</label>
                            <input id="db_username" type="text" name="db_username" value="{{ old('db_username') }}" required maxlength="255" placeholder="root">
                            @error('db_username')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="db_password">DB password</label>
                            <input id="db_password" type="password" name="db_password" value="{{ old('db_password') }}" maxlength="255" placeholder="Leave blank if none" autocomplete="new-password">
                            @error('db_password')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="register-section">
                        <h2 class="register-section-title">Mail</h2>
                        <div class="field">
                            <label for="mail_mailer">Mail driver</label>
                            <select id="mail_mailer" name="mail_mailer">
                                <option value="smtp" {{ old('mail_mailer', 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="log" {{ old('mail_mailer') === 'log' ? 'selected' : '' }}>Log (no real emails)</option>
                            </select>
                            @error('mail_mailer')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_host">SMTP host</label>
                            <input id="mail_host" type="text" name="mail_host" value="{{ old('mail_host', '127.0.0.1') }}" maxlength="255" placeholder="smtp.mailtrap.io">
                            @error('mail_host')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_port">SMTP port</label>
                            <input id="mail_port" type="text" name="mail_port" value="{{ old('mail_port', '2525') }}" maxlength="10" placeholder="2525">
                            @error('mail_port')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_username">SMTP username</label>
                            <input id="mail_username" type="text" name="mail_username" value="{{ old('mail_username') }}" maxlength="255">
                            @error('mail_username')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_password">SMTP password</label>
                            <input id="mail_password" type="password" name="mail_password" value="{{ old('mail_password') }}" maxlength="255" autocomplete="new-password">
                            @error('mail_password')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_from_address">From address</label>
                            <input id="mail_from_address" type="text" name="mail_from_address" value="{{ old('mail_from_address', 'hello@example.com') }}" maxlength="255" placeholder="noreply@yourdomain.com">
                            @error('mail_from_address')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="mail_from_name">From name</label>
                            <input id="mail_from_name" type="text" name="mail_from_name" value="{{ old('mail_from_name') }}" maxlength="255" placeholder="Builder Platform">
                            @error('mail_from_name')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="register-section">
                        <h2 class="register-section-title">Super Admin</h2>
                        <p class="login-subtitle" style="margin: 0 0 1rem 0;">Create the first account to log in after installation.</p>
                        <div class="field">
                            <label for="admin_name">Name</label>
                            <input id="admin_name" type="text" name="admin_name" value="{{ old('admin_name', 'Super Admin') }}" maxlength="255" placeholder="Super Admin">
                            @error('admin_name')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="admin_email">Email</label>
                            <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin@yourdomain.com">
                            @error('admin_email')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="admin_password">Password</label>
                            <input id="admin_password" type="password" name="admin_password" required minlength="8" placeholder="Min 8 characters" autocomplete="new-password">
                            @error('admin_password')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="admin_password_confirmation">Confirm password</label>
                            <input id="admin_password_confirmation" type="password" name="admin_password_confirmation" required minlength="8" placeholder="Repeat password" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="login-actions" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);">
                        <button type="submit" class="btn-primary">Install</button>
                        <a href="{{ url('/') }}" class="login-back">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
