<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CP Registration — {{ $builder->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .login-logo-img { width: 48px; height: 48px; object-fit: contain; display: block; }
        .login-page { background: {{ $builder->getRegistrationPageBg() }}; }
        .login-card { background: {{ $builder->getRegistrationCardBg() }}; }
        .login-logo-icon { background: {{ $builder->getPrimaryColor() ?? '#2d5f5f' }}; }
        .login-title { color: {{ $builder->getRegistrationTitleColor() }}; }
        .login-subtitle { color: {{ $builder->getRegistrationSubtitleColor() }}; }
        .login-form label { color: {{ $builder->getRegistrationTextColor() }}; }
        .login-logo-text { color: {{ $builder->getRegistrationTitleColor() }}; }
        .btn-primary { background: {{ $builder->getPrimaryColor() ?? '#2d5f5f' }}; }
        .btn-primary:hover { background: {{ $builder->getPrimaryColor() ?? '#2d5f5f' }}; opacity: 0.95; }
        .login-back:hover { color: {{ $builder->getPrimaryColor() ?? '#2d5f5f' }}; }
    </style>
</head>
<body>
    <div class="login-page">
        <div class="login-container">
            <div class="login-card">
                <div class="login-logo">
                    @if($builder->getLogoUrl())
                        <img src="{{ $builder->getLogoUrl() }}" alt="" class="login-logo-img" width="48" height="48">
                    @else
                        <span class="login-logo-icon">BP</span>
                    @endif
                    <span class="login-logo-text">{{ $builder->name }}</span>
                </div>
                <h1 class="login-title">Channel Partner Registration</h1>
                <p class="login-subtitle">Submit your details for verification. You will appear in the customer form once approved.</p>

                @if(session('success'))
                    <p style="margin: 0 0 1rem 0; color: var(--success); font-weight: 500;">{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p style="margin: 0 0 1rem 0; color: var(--error); font-weight: 500;">{{ session('error') }}</p>
                @endif
                @if(session('message'))
                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary);">{{ session('message') }}</p>
                @endif

                <form method="POST" action="{{ route('register.cp', $builder->slug) }}" class="login-form" enctype="multipart/form-data">
                    @csrf
                    @if($fields->isEmpty())
                        <div class="field">
                            <label for="name">Name *</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                            @error('name')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="email">Email *</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                            @error('email')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="password">Password *</label>
                            <input id="password" type="password" name="password" required>
                            @error('password')<p class="login-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="field">
                            <label for="password_confirmation">Confirm password *</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required>
                        </div>
                        <div class="field">
                            <label for="phone">Contact number</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="field">
                            <label for="company_name">Company / Firm name</label>
                            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}">
                        </div>
                    @else
                        @foreach($fields as $field)
                            <div class="field">
                                <label for="field_{{ $field->id }}">{{ $field->label }}{{ $field->required ? ' *' : '' }}</label>
                                @if($field->type === 'textarea')
                                    <textarea id="field_{{ $field->id }}" name="{{ $field->key }}" {{ $field->required ? 'required' : '' }} placeholder="{{ $field->placeholder }}">{{ old($field->key) }}</textarea>
                                @elseif($field->type === 'dropdown')
                                    <select id="field_{{ $field->id }}" name="{{ $field->key }}" {{ $field->required ? 'required' : '' }}>
                                        <option value="">Select</option>
                                        @foreach($field->options ?? [] as $opt)
                                            <option value="{{ $opt }}" {{ old($field->key) === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field->type === 'file')
                                    <input id="field_{{ $field->id }}" type="file" name="{{ $field->key }}" {{ $field->required ? 'required' : '' }}>
                                @else
                                    <input id="field_{{ $field->id }}" type="{{ $field->type === 'date' ? 'date' : ($field->key === 'password' ? 'password' : 'text') }}" name="{{ $field->key }}" value="{{ old($field->key) }}" {{ $field->required ? 'required' : '' }} placeholder="{{ $field->placeholder }}">
                                @endif
                                @error($field->key)<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                        @if($fields->contains('key', 'password'))
                            {{-- Form has password field but no confirmation - add Confirm password for validation --}}
                            <div class="field">
                                <label for="password_confirmation">Confirm password *</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Re-enter password">
                                @error('password_confirmation')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                        @else
                            <div class="field">
                                <label for="password">Password *</label>
                                <input id="password" type="password" name="password" required>
                                @error('password')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="password_confirmation">Confirm password *</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Re-enter password">
                                @error('password_confirmation')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                        @endif
                    @endif
                    <div class="login-actions">
                        <button type="submit" class="btn-primary">Submit</button>
                        <a href="{{ url('/') }}" class="login-back">← Back to home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
