<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormController extends Controller
{
    /** @return BuilderFirm|RedirectResponse */
    private function getBuilderAndAuthorize(string $slug): BuilderFirm|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        return $builder;
    }

    public function create(Request $request, string $slug): View|RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        return view('dashboard.form-edit', [
            'user' => session('user'),
            'tenant' => $builder,
            'form' => null,
        ]);
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:cp_registration,customer_registration',
        ]);
        $builder->forms()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_active' => false,
        ]);
        return redirect()->route('tenant.forms.index', $slug)->with('success', 'Form created.');
    }

    public function edit(string $slug, Form $form): View|RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $form->load('formFields');
        return view('dashboard.form-edit', [
            'user' => session('user'),
            'tenant' => $builder,
            'form' => $form,
        ]);
    }

    public function update(Request $request, string $slug, Form $form): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $form->update($validated);
        return redirect()->route('tenant.forms.edit', [$slug, $form])->with('success', 'Form updated.');
    }

    public function destroy(string $slug, Form $form): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $form->delete();
        return redirect()->route('tenant.forms.index', $slug)->with('success', 'Form deleted.');
    }

    public function setActive(string $slug, Form $form): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        Form::where('builder_firm_id', $builder->id)->where('type', $form->type)->update(['is_active' => false]);
        $form->update(['is_active' => true]);
        return redirect()->route('tenant.forms.index', $slug)->with('success', 'Form set as active.');
    }

    public function storeField(Request $request, string $slug, Form $form): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'key' => 'required|string|max:100',
            'type' => 'required|in:text,number,email,textarea,date,dropdown,file',
            'required' => 'nullable|boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);
        $maxOrder = $form->formFields()->max('order') ?? 0;
        $form->formFields()->create([
            'label' => $validated['label'],
            'key' => $validated['key'],
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => isset($validated['options']) && $validated['options'] !== '' ? array_map('trim', explode("\n", $validated['options'])) : null,
            'order' => (int) ($validated['order'] ?? $maxOrder + 1),
        ]);
        return redirect()->route('tenant.forms.edit', [$slug, $form])->with('success', 'Field added.');
    }

    public function updateField(Request $request, string $slug, Form $form, FormField $formField): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id || (int) $formField->form_id !== (int) $form->id) {
            abort(404);
        }
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'key' => 'required|string|max:100',
            'type' => 'required|in:text,number,email,textarea,date,dropdown,file',
            'required' => 'nullable|boolean',
            'placeholder' => 'nullable|string|max:255',
            'options' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
        ]);
        $formField->update([
            'label' => $validated['label'],
            'key' => $validated['key'],
            'type' => $validated['type'],
            'required' => (bool) ($validated['required'] ?? false),
            'placeholder' => $validated['placeholder'] ?? null,
            'options' => isset($validated['options']) && $validated['options'] !== '' ? array_map('trim', explode("\n", $validated['options'])) : null,
            'order' => (int) ($validated['order'] ?? $formField->order),
        ]);
        return redirect()->route('tenant.forms.edit', [$slug, $form])->with('success', 'Field updated.');
    }

    public function destroyField(string $slug, Form $form, FormField $formField): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        if ((int) $form->builder_firm_id !== (int) $builder->id || (int) $formField->form_id !== (int) $form->id) {
            abort(404);
        }
        $formField->delete();
        return redirect()->route('tenant.forms.edit', [$slug, $form])->with('success', 'Field removed.');
    }

    /** Create a new form from default template. */
    public function createFromTemplate(Request $request, string $slug): RedirectResponse
    {
        $builder = $this->getBuilderAndAuthorize($slug);
        if ($builder instanceof RedirectResponse) {
            return $builder;
        }
        $type = $request->validate(['type' => 'required|in:cp_registration,customer_registration'])['type'];

        $templates = self::getDefaultTemplates();
        $template = $templates[$type] ?? null;
        if (! $template) {
            return redirect()->route('tenant.forms.index', $slug)->with('error', 'Unknown template.');
        }

        $form = $builder->forms()->create([
            'name' => $template['name'],
            'type' => $type,
            'is_active' => false,
        ]);
        foreach ($template['fields'] as $i => $field) {
            $form->formFields()->create([
                'label' => $field['label'],
                'key' => $field['key'],
                'type' => $field['type'],
                'required' => $field['required'] ?? false,
                'placeholder' => $field['placeholder'] ?? null,
                'options' => $field['options'] ?? null,
                'order' => $i + 1,
            ]);
        }
        return redirect()->route('tenant.forms.edit', [$slug, $form])->with('success', 'Form created from template. Add or edit fields and set as active when ready.');
    }

    public static function getDefaultTemplates(): array
    {
        return [
            'cp_registration' => [
                'name' => 'CP Registration',
                'fields' => [
                    ['label' => 'Name', 'key' => 'name', 'type' => 'text', 'required' => true, 'placeholder' => 'Full name'],
                    ['label' => 'Email', 'key' => 'email', 'type' => 'email', 'required' => true, 'placeholder' => 'Email'],
                    ['label' => 'Password', 'key' => 'password', 'type' => 'text', 'required' => true, 'placeholder' => 'Password'],
                    ['label' => 'Contact number', 'key' => 'phone', 'type' => 'text', 'required' => false, 'placeholder' => 'Phone'],
                    ['label' => 'Firm / Individual', 'key' => 'firm_individual', 'type' => 'text', 'required' => false],
                    ['label' => 'Company name', 'key' => 'company_name', 'type' => 'text', 'required' => false],
                    ['label' => 'Address', 'key' => 'address', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Bank details', 'key' => 'bank_details', 'type' => 'textarea', 'required' => false],
                    ['label' => 'GST number', 'key' => 'gst_number', 'type' => 'text', 'required' => false],
                    ['label' => 'RERA number', 'key' => 'rera_number', 'type' => 'text', 'required' => false],
                    ['label' => 'Documents', 'key' => 'documents', 'type' => 'file', 'required' => false],
                ],
            ],
            'customer_registration' => [
                'name' => 'Customer Registration',
                'fields' => [
                    ['label' => 'Client name', 'key' => 'name', 'type' => 'text', 'required' => true],
                    ['label' => 'Contact number', 'key' => 'mobile', 'type' => 'text', 'required' => true],
                    ['label' => 'Email', 'key' => 'email', 'type' => 'email', 'required' => false],
                    ['label' => 'Occupation', 'key' => 'occupation', 'type' => 'text', 'required' => false],
                    ['label' => 'Current city', 'key' => 'city', 'type' => 'text', 'required' => false],
                    ['label' => 'Visit date', 'key' => 'visit_date', 'type' => 'date', 'required' => false],
                    ['label' => 'Preferred size', 'key' => 'preferred_size', 'type' => 'text', 'required' => false],
                    ['label' => 'Budget', 'key' => 'budget', 'type' => 'number', 'required' => false],
                    ['label' => 'Budget currency', 'key' => 'budget_currency', 'type' => 'text', 'required' => false],
                    ['label' => 'Purpose', 'key' => 'purpose', 'type' => 'text', 'required' => false],
                    ['label' => 'Remark', 'key' => 'notes', 'type' => 'textarea', 'required' => false],
                    ['label' => 'Image', 'key' => 'image', 'type' => 'file', 'required' => false],
                ],
            ],
        ];
    }
}
