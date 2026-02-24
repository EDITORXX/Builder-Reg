@extends('layouts.app')

@section('title', $form ? 'Edit form' : 'New form')
@section('heading', $form ? 'Edit form' : 'New form')
@section('subtitle', $form ? $form->name : 'Create a new form')

@section('content')
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-body">
            @if(session('success'))
                <p style="margin: 0 0 1rem 0; color: var(--success);">{{ session('success') }}</p>
            @endif

            @if($form === null)
                <form method="POST" action="{{ route('tenant.forms.store', $tenant->slug) }}">
                    @csrf
                    <div style="margin-bottom: 1rem;">
                        <label for="name" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Name *</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required maxlength="255" style="width: 100%; max-width: 320px; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                        @error('name')<p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>@enderror
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label for="type" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Type *</label>
                        <select id="type" name="type" required style="padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                            <option value="cp_registration" {{ old('type') === 'cp_registration' ? 'selected' : '' }}>CP Registration</option>
                            <option value="customer_registration" {{ old('type') === 'customer_registration' ? 'selected' : '' }}>Customer Registration</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">Create form</button>
                    <a href="{{ route('tenant.forms.index', $tenant->slug) }}" style="margin-left: 0.75rem; font-size: 0.875rem;">Cancel</a>
                </form>
            @else
                <form method="POST" action="{{ route('tenant.forms.update', [$tenant->slug, $form]) }}" style="margin-bottom: 1.5rem;">
                    @csrf
                    @method('PUT')
                    <div style="margin-bottom: 1rem;">
                        <label for="name" style="display: block; font-size: 0.875rem; margin-bottom: 0.25rem;">Form name *</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $form->name) }}" required maxlength="255" style="width: 100%; max-width: 320px; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);">
                        @error('name')<p style="margin: 0.25rem 0 0; font-size: 0.8125rem; color: var(--error);">{{ $message }}</p>@enderror
                    </div>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0 0 0.5rem 0;">Type: {{ str_replace('_', ' ', $form->type) }}</p>
                    <button type="submit" class="btn-primary">Save name</button>
                </form>

                @if(!$form->is_active)
                    <form method="POST" action="{{ route('tenant.forms.activate', [$tenant->slug, $form]) }}" style="margin-bottom: 1.5rem;">
                        @csrf
                        <button type="submit" class="btn-primary">Set as active form (for this type)</button>
                    </form>
                @else
                    <p style="margin-bottom: 1.5rem; font-size: 0.875rem; color: var(--success);">This form is currently active for {{ str_replace('_', ' ', $form->type) }}.</p>
                @endif

                <h3 style="font-size: 1rem; margin: 1.5rem 0 0.75rem 0;">Fields</h3>
                @if($form->formFields->isEmpty())
                    <p style="margin: 0 0 1rem 0; color: var(--text-secondary); font-size: 0.875rem;">No fields yet. Add one below.</p>
                @else
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem; margin-bottom: 1rem;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--border); text-align: left;">
                                <th style="padding: 0.5rem 0;">Label</th>
                                <th style="padding: 0.5rem 0;">Key</th>
                                <th style="padding: 0.5rem 0;">Type</th>
                                <th style="padding: 0.5rem 0;">Required</th>
                                <th style="padding: 0.5rem 0;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($form->formFields as $field)
                                <tr style="border-bottom: 1px solid var(--border);">
                                    <td style="padding: 0.5rem 0;">{{ $field->label }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $field->key }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $field->type }}</td>
                                    <td style="padding: 0.5rem 0;">{{ $field->required ? 'Yes' : 'No' }}</td>
                                    <td style="padding: 0.5rem 0;">
                                        <a href="{{ route('tenant.forms.edit', [$tenant->slug, $form]) }}?edit_field={{ $field->id }}" style="margin-right: 0.5rem;">Edit</a>
                                        <form method="POST" action="{{ route('tenant.forms.fields.destroy', [$tenant->slug, $form, $field]) }}" style="display: inline-block;" onsubmit="return confirm('Remove this field?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="font-size: 0.8125rem; background: none; border: none; cursor: pointer; color: var(--error);">Remove</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                @if(isset($editField) && $editField)
                <h4 style="font-size: 0.9375rem; margin: 1rem 0 0.5rem 0;">Edit field: {{ $editField->label }}</h4>
                <form method="POST" action="{{ route('tenant.forms.fields.update', [$tenant->slug, $form, $editField]) }}" style="margin-bottom: 1.5rem;">
                    @csrf
                    @method('PUT')
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; margin-bottom: 0.5rem;">
                        <div><label style="display: block; font-size: 0.8125rem;">Label</label><input type="text" name="label" required maxlength="255" value="{{ old('label', $editField->label) }}" style="width: 140px; padding: 0.375rem 0.5rem;"></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Key</label><input type="text" name="key" required maxlength="100" value="{{ old('key', $editField->key) }}" style="width: 120px; padding: 0.375rem 0.5rem;"></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Type</label>
                            <select name="type" style="padding: 0.375rem 0.5rem;">
                                @foreach(['text', 'number', 'email', 'textarea', 'date', 'dropdown', 'file'] as $t)
                                    <option value="{{ $t }}" {{ old('type', $editField->type) === $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><label style="display: block; font-size: 0.8125rem;">Required</label><input type="checkbox" name="required" value="1" {{ old('required', $editField->required) ? 'checked' : '' }}></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Placeholder</label><input type="text" name="placeholder" maxlength="255" value="{{ old('placeholder', $editField->placeholder) }}" style="width: 120px; padding: 0.375rem 0.5rem;"></div>
                        <button type="submit" class="btn-primary" style="padding: 0.375rem 0.75rem;">Save field</button>
                        <a href="{{ route('tenant.forms.edit', [$tenant->slug, $form]) }}" style="font-size: 0.875rem;">Cancel</a>
                    </div>
                    <div style="margin-top: 0.5rem;"><label style="font-size: 0.8125rem;">Options (for dropdown, one per line)</label><textarea name="options" rows="3" style="width: 100%; max-width: 320px; padding: 0.375rem 0.5rem;">{{ old('options', is_array($editField->options) ? implode("\n", $editField->options) : '') }}</textarea></div>
                </form>
                @endif

                <h4 style="font-size: 0.9375rem; margin: 1rem 0 0.5rem 0;">Add field</h4>
                <form method="POST" action="{{ route('tenant.forms.fields.store', [$tenant->slug, $form]) }}">
                    @csrf
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end; margin-bottom: 0.5rem;">
                        <div><label style="display: block; font-size: 0.8125rem;">Label</label><input type="text" name="label" required maxlength="255" style="width: 140px; padding: 0.375rem 0.5rem;"></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Key</label><input type="text" name="key" required maxlength="100" style="width: 120px; padding: 0.375rem 0.5rem;"></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Type</label>
                            <select name="type" style="padding: 0.375rem 0.5rem;">
                                <option value="text">text</option>
                                <option value="number">number</option>
                                <option value="email">email</option>
                                <option value="textarea">textarea</option>
                                <option value="date">date</option>
                                <option value="dropdown">dropdown</option>
                                <option value="file">file</option>
                            </select>
                        </div>
                        <div><label style="display: block; font-size: 0.8125rem;">Required</label><input type="checkbox" name="required" value="1"></div>
                        <div><label style="display: block; font-size: 0.8125rem;">Placeholder</label><input type="text" name="placeholder" maxlength="255" style="width: 120px; padding: 0.375rem 0.5rem;"></div>
                        <button type="submit" class="btn-primary" style="padding: 0.375rem 0.75rem;">Add field</button>
                    </div>
                    <div style="margin-top: 0.5rem;"><label style="font-size: 0.8125rem;">Options (for dropdown, one per line)</label><textarea name="options" rows="2" style="width: 100%; max-width: 320px; padding: 0.375rem 0.5rem;"></textarea></div>
                </form>

                <p style="margin-top: 1.5rem;"><a href="{{ route('tenant.forms.index', $tenant->slug) }}">Back to Forms</a></p>
            @endif
        </div>
    </div>
@endsection
