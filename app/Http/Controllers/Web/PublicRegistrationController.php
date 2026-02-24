<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BuilderFirm;
use App\Models\ChannelPartner;
use App\Models\CpApplication;
use App\Models\Customer;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use App\Notifications\NewCustomerRegisteredNotification;
use App\Services\LockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PublicRegistrationController extends Controller
{
    public function showCpForm(string $builder_slug): View|RedirectResponse
    {
        $builder = BuilderFirm::where('slug', $builder_slug)->where('is_active', true)->firstOrFail();
        $form = Form::getActiveForBuilder($builder, Form::TYPE_CP_REGISTRATION);
        $fields = $form?->formFields ?? collect();
        return view('register.cp', [
            'builder' => $builder,
            'form' => $form,
            'fields' => $fields,
        ]);
    }

    public function submitCpForm(Request $request, string $builder_slug): RedirectResponse
    {
        $builder = BuilderFirm::where('slug', $builder_slug)->where('is_active', true)->firstOrFail();
        $form = Form::getActiveForBuilder($builder, Form::TYPE_CP_REGISTRATION);
        $fields = $form?->formFields ?? collect();

        // If broker already has pending/approved application for this builder, show message immediately
        if ($request->filled('email')) {
            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser && $existingUser->channelPartner) {
                $existingApp = CpApplication::where('channel_partner_id', $existingUser->channelPartner->id)
                    ->where('builder_firm_id', $builder->id)
                    ->first();
                if ($existingApp) {
                    if ($existingApp->status === CpApplication::STATUS_PENDING) {
                        return redirect()->back()->withInput($request->except('password', 'password_confirmation'))
                            ->with('error', 'Aapka application is builder ke liye pehle se pending hai. Verification ka intezaar karein.');
                    }
                    if ($existingApp->status === CpApplication::STATUS_APPROVED) {
                        return redirect()->back()->with('message', 'Aap is builder ke liye pehle se approved hain. Customer form mein aap select ho sakte hain.');
                    }
                }
            }
        }

        $rules = ['name' => 'required|string|max:255', 'email' => 'required|email|max:255'];
        if (! User::where('email', $request->email)->exists()) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }
        foreach ($fields as $field) {
            $key = $field->key;
            if (in_array($field->key, ['password', 'password_confirmation'], true)) {
                continue;
            }
            $rule = $field->required ? 'required|' : 'nullable|';
            $rule .= match ($field->type) {
                'email' => 'email|max:255',
                'number' => 'numeric',
                'file' => 'nullable|file|max:5120',
                default => 'string|max:1000',
            };
            $rules[$key] = $rule;
        }
        $validated = $request->validate($rules);

        $user = User::where('email', $validated['email'])->first();
        if ($user) {
            $cp = $user->channelPartner;
            if (! $cp) {
                $cp = ChannelPartner::create(['user_id' => $user->id, 'firm_name' => $validated['company_name'] ?? $validated['firm_name'] ?? null, 'rera_number' => $validated['rera_number'] ?? null, 'gst_number' => $validated['gst_number'] ?? null]);
            } else {
                $cp->update(['firm_name' => $validated['company_name'] ?? $validated['firm_name'] ?? $cp->firm_name, 'rera_number' => $validated['rera_number'] ?? $cp->rera_number, 'gst_number' => $validated['gst_number'] ?? $cp->gst_number]);
            }
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password'] ?? str()->random(12)),
                'role' => User::ROLE_CHANNEL_PARTNER,
                'builder_firm_id' => null,
                'is_active' => true,
            ]);
            $cp = ChannelPartner::create([
                'user_id' => $user->id,
                'firm_name' => $validated['company_name'] ?? $validated['firm_name'] ?? null,
                'rera_number' => $validated['rera_number'] ?? null,
                'gst_number' => $validated['gst_number'] ?? null,
            ]);
        }

        $existingApp = CpApplication::where('channel_partner_id', $cp->id)->where('builder_firm_id', $builder->id)->first();
        if ($existingApp) {
            if ($existingApp->status === CpApplication::STATUS_PENDING) {
                return redirect()->back()->with('message', 'You have already applied. Your application is pending verification.');
            }
            if ($existingApp->status === CpApplication::STATUS_APPROVED) {
                return redirect()->back()->with('message', 'You are already approved for this builder.');
            }
        }

        $app = CpApplication::create([
            'channel_partner_id' => $cp->id,
            'builder_firm_id' => $builder->id,
            'status' => CpApplication::STATUS_PENDING,
        ]);

        if ($form) {
            FormSubmission::create([
                'form_id' => $form->id,
                'builder_firm_id' => $builder->id,
                'data' => $validated,
                'submissible_type' => 'cp_application',
                'submissible_id' => $app->id,
            ]);
        }

        return redirect()->back()->with('success', 'Submitted for verification.');
    }

    public function showCustomerForm(string $builder_slug): View|RedirectResponse
    {
        $builder = BuilderFirm::where('slug', $builder_slug)->where('is_active', true)->firstOrFail();
        $form = Form::getActiveForBuilder($builder, Form::TYPE_CUSTOMER_REGISTRATION);
        $fields = $form?->formFields ?? collect();
        $projects = Project::where('builder_firm_id', $builder->id)->where('status', 'active')->orderBy('name')->get();
        $verifiedCps = CpApplication::where('builder_firm_id', $builder->id)
            ->where('status', CpApplication::STATUS_APPROVED)
            ->with('channelPartner.user')
            ->get()
            ->map(fn ($a) => ['id' => $a->channel_partner_id, 'name' => $a->channelPartner?->firm_name ?: $a->channelPartner?->user?->name ?: 'CP #' . $a->channel_partner_id]);
        $verifiedCps = collect([['id' => 'walkin', 'name' => 'Walk-in']])->concat($verifiedCps);

        return view('register.customer', [
            'builder' => $builder,
            'form' => $form,
            'fields' => $fields,
            'projects' => $projects,
            'verifiedCps' => $verifiedCps,
            'scheduled_visit_enabled' => (bool) ($builder->scheduled_visit_enabled ?? false),
        ]);
    }

    public function showCustomerScan(string $builder_slug): View|\Illuminate\Http\Response
    {
        $builder = BuilderFirm::where('slug', $builder_slug)->where('is_active', true)->firstOrFail();
        if (! ($builder->scheduled_visit_enabled ?? false)) {
            abort(404);
        }
        return view('register.customer_scan', ['builder' => $builder]);
    }

    public function submitCustomerForm(Request $request, string $builder_slug): RedirectResponse
    {
        $builder = BuilderFirm::where('slug', $builder_slug)->where('is_active', true)->firstOrFail();
        $form = Form::getActiveForBuilder($builder, Form::TYPE_CUSTOMER_REGISTRATION);
        $fields = $form?->formFields ?? collect();

        $verifiedCpIds = CpApplication::where('builder_firm_id', $builder->id)->where('status', CpApplication::STATUS_APPROVED)->pluck('channel_partner_id')->toArray();
        $allowedCpIds = array_merge(['walkin'], $verifiedCpIds);

        $rules = [
            'project_id' => 'required|exists:projects,id',
            'cp_id' => 'required|in:' . implode(',', $allowedCpIds),
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
        ];
        foreach ($fields as $field) {
            if (in_array($field->key, ['project_id', 'cp_id', 'name', 'mobile', 'budget_currency'], true)) {
                continue;
            }
            $key = $field->key;
            $rule = $field->required ? 'required|' : 'nullable|';
            $rule .= match ($field->type) {
                'email' => 'email|max:255',
                'number' => 'numeric',
                'file' => 'nullable|file|max:5120',
                default => 'string|max:2000',
            };
            $rules[$key] = $rule;
        }
        $validated = $request->validate($rules);

        $project = Project::findOrFail($validated['project_id']);
        if ((int) $project->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }

        $normalizedMobile = Customer::normalizeMobile($validated['mobile']);
        $isWalkin = ($validated['cp_id'] ?? '') === 'walkin';
        $currentCpId = $isWalkin ? null : (int) $validated['cp_id'];
        $lockCheck = app(LockService::class)->checkLockAndDuplicate(
            (int) $validated['project_id'],
            $normalizedMobile,
            $currentCpId
        );
        if (! $lockCheck['allowed']) {
            return redirect()->back()
                ->withInput($request->except('mobile'))
                ->with('error', $lockCheck['message']);
        }

        $customer = Customer::firstOrCreate(
            ['mobile' => $normalizedMobile],
            [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'city' => $validated['city'] ?? null,
            ]
        );
        if (! $customer->wasRecentlyCreated) {
            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $customer->email,
                'city' => $validated['city'] ?? $customer->city,
            ]);
        }

        $source = $lockCheck['is_revisit']
            ? Lead::SOURCE_REVISIT
            : ($isWalkin ? Lead::SOURCE_DIRECT : Lead::SOURCE_CHANNEL_PARTNER);
        $lead = Lead::create([
            'project_id' => $validated['project_id'],
            'customer_id' => $customer->id,
            'channel_partner_id' => $isWalkin ? null : $validated['cp_id'],
            'created_by' => null,
            'status' => Lead::STATUS_NEW,
            'sales_status' => Lead::SALES_NEW,
            'source' => $source,
            'budget' => $validated['budget'] ?? null,
            'property_type' => $validated['property_type'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($form) {
            FormSubmission::create([
                'form_id' => $form->id,
                'builder_firm_id' => $builder->id,
                'data' => $validated,
                'submissible_type' => 'lead',
                'submissible_id' => $lead->id,
            ]);
        }

        if ($lead->channel_partner_id) {
            $lead->load(['channelPartner.user', 'project', 'customer']);
            $cpUser = $lead->channelPartner?->user;
            if ($cpUser && $cpUser->email) {
                $cpUser->notify(new NewCustomerRegisteredNotification($builder, $lead));
            }
        }

        $successMessage = $lockCheck['is_revisit']
            ? 'Revisit lead create ho gaya. Thank you.'
            : 'Registration submitted. Thank you.';
        return redirect()->back()->with('success', $successMessage);
    }
}
