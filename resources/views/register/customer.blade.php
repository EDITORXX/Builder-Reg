<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Registration — {{ $builder->name }}</title>
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
                <h1 class="login-title">Customer Registration</h1>
                <p class="login-subtitle">Register your visit. Select project and channel partner, then fill your details.</p>

                @if(!empty($scheduled_visit_enabled))
                    <p style="margin: 0 0 1rem 0; padding: 0.75rem 1rem; background: var(--bg-page); border: 1px solid var(--border); border-radius: var(--radius); font-size: 0.9375rem;">
                        Have a scheduled visit? <a href="{{ route('register.customer.scan', $builder->slug) }}" style="font-weight: 600;">Scan QR to check-in</a>
                    </p>
                @endif

                @if(session('success'))
                    <p class="msg-success">{{ session('success') }}</p>
                @endif
                @if(session('error'))
                    <p style="margin: 0 0 1rem 0; padding: 0.75rem 1rem; background: rgb(220 38 38 / 0.08); border-radius: var(--radius); font-size: 0.9375rem; color: var(--error);">{{ session('error') }}</p>
                @endif

                @if($projects->isEmpty())
                    <p style="margin: 0; padding: 0.75rem 1rem; background: rgb(220 38 38 / 0.08); border-radius: var(--radius); font-size: 0.9375rem; color: var(--error);">No projects available. Please contact builder/admin.</p>
                @else
                    <form method="POST" action="{{ route('register.customer', $builder->slug) }}" class="login-form" enctype="multipart/form-data">
                        @csrf
                        <div class="register-section">
                            <h2 class="register-section-title">Project & Channel Partner</h2>
                            <div class="field">
                                <label for="project_id">Project <span aria-hidden="true">*</span></label>
                                <select id="project_id" name="project_id" required aria-required="true">
                                    <option value="">Select project</option>
                                    @foreach($projects as $p)
                                        <option value="{{ $p->id }}" {{ old('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('project_id')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="cp_id">Channel Partner (CP) <span aria-hidden="true">*</span></label>
                                <select id="cp_id" name="cp_id" required aria-required="true">
                                    <option value="walkin" {{ old('cp_id', 'walkin') == 'walkin' ? 'selected' : '' }}>Walk-in</option>
                                    @foreach($verifiedCps as $cp)
                                        @if($cp['id'] !== 'walkin')
                                            <option value="{{ $cp['id'] }}" {{ old('cp_id') == $cp['id'] ? 'selected' : '' }}>{{ $cp['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('cp_id')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="register-section">
                            <h2 class="register-section-title">Your details</h2>
                        @if($fields->isEmpty())
                            <div class="field">
                                <label for="name">Client name <span aria-hidden="true">*</span></label>
                                <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="Full name" autocomplete="name">
                                @error('name')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="mobile">Contact number <span aria-hidden="true">*</span></label>
                                <input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}" required placeholder="10-digit mobile number" inputmode="tel" autocomplete="tel">
                                @error('mobile')<p class="login-error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="email@example.com" autocomplete="email">
                            </div>
                            <div class="field">
                                <label for="city">City</label>
                                <input id="city" type="text" name="city" value="{{ old('city') }}" placeholder="Current city" autocomplete="address-level2">
                            </div>
                            <div class="field">
                                <label for="notes">Notes</label>
                                <textarea id="notes" name="notes" placeholder="Any remarks (optional)" rows="3">{{ old('notes') }}</textarea>
                            </div>
                        @else
                            @foreach($fields as $field)
                                @if(in_array($field->key, ['project_id', 'cp_id'], true))
                                    @continue
                                @endif
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
                                    @elseif($field->type === 'date')
                                        <input id="field_{{ $field->id }}" type="date" name="{{ $field->key }}" value="{{ old($field->key, date('Y-m-d')) }}" {{ $field->required ? 'required' : '' }} readonly style="background: var(--bg-page); cursor: not-allowed;">
                                    @else
                                        <input id="field_{{ $field->id }}" type="{{ $field->type === 'number' ? 'number' : 'text' }}" name="{{ $field->key }}" value="{{ old($field->key) }}" {{ $field->required ? 'required' : '' }} placeholder="{{ $field->placeholder }}">
                                    @endif
                                    @error($field->key)<p class="login-error">{{ $message }}</p>@enderror
                                </div>
                            @endforeach
                            @if(!$fields->contains('key', 'name'))
                                <div class="field">
                                    <label for="name">Name *</label>
                                    <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                                    @error('name')<p class="login-error">{{ $message }}</p>@enderror
                                </div>
                            @endif
                            @if(!$fields->contains('key', 'mobile'))
                                <div class="field">
                                    <label for="mobile">Contact number *</label>
                                    <input id="mobile" type="text" name="mobile" value="{{ old('mobile') }}" required>
                                    @error('mobile')<p class="login-error">{{ $message }}</p>@enderror
                                </div>
                            @endif
                        @endif
                        <div class="login-actions" style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border);">
                            <button type="submit" class="btn-primary">Submit</button>
                            <a href="{{ url('/') }}" class="login-back">← Back to home</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
