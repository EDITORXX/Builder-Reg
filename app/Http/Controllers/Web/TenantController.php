<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\FormController;
use App\Models\BuilderFirm;
use App\Models\ChannelPartner;
use App\Models\CpApplication;
use App\Models\Form;
use App\Models\FormField;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Project;
use App\Models\User;
use App\Models\Visit;
use App\Notifications\CpApplicationApprovedNotification;
use App\Notifications\CpApplicationRejectedNotification;
use App\Services\DashboardService;
use App\Services\ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    private function requireSuperAdmin(): RedirectResponse|null
    {
        $user = session('user');
        if (! session('api_token') || ! $user) {
            return redirect()->route('login');
        }
        if (! $user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can manage tenants.');
        }
        return null;
    }

    public function index(Request $request): View|RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $tenants = BuilderFirm::with(['plan', 'users'])->orderBy('name')->get();
        return view('tenants.index', ['tenants' => $tenants]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $plans = Plan::where('is_active', true)->orderBy('max_users')->get();
        return view('tenants.create', ['plans' => $plans]);
    }

    public function store(Request $request): RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'plan_id' => 'required|exists:plans,id',
            'logo_url' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
        ]);

        $slug = Str::slug($validated['name']) . '-' . substr(uniqid(), -4);
        $settings = array_filter([
            'logo_url' => $validated['logo_url'] ?? null,
            'primary_color' => $validated['primary_color'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenant-logos', 'public');
            $settings['logo_url'] = asset('storage/' . $path);
        }

        $builder = BuilderFirm::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'address' => null,
            'default_lock_days' => 30,
            'settings' => $settings ?: [],
            'is_active' => true,
            'plan_id' => $validated['plan_id'],
        ]);

        User::create([
            'name' => $validated['name'] . ' Admin',
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_BUILDER_ADMIN,
            'builder_firm_id' => $builder->id,
            'is_active' => true,
        ]);

        $templates = FormController::getDefaultTemplates();
        foreach (['cp_registration', 'customer_registration'] as $type) {
            $template = $templates[$type];
            $form = $builder->forms()->create([
                'name' => $template['name'],
                'type' => $type,
                'is_active' => true,
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
        }

        return redirect()->route('tenants.index')->with('success', 'Tenant created. Dashboard: ' . url("/t/{$builder->slug}"));
    }

    public function edit(BuilderFirm $tenant): View|RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $plans = Plan::where('is_active', true)->orderBy('max_users')->get();
        return view('tenants.edit', ['tenant' => $tenant, 'plans' => $plans]);
    }

    public function update(Request $request, BuilderFirm $tenant): RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
        $tenant->update(['plan_id' => $validated['plan_id']]);
        return redirect()->route('tenants.index')->with('success', 'Plan updated for ' . $tenant->name);
    }

    public function resetAdminPassword(BuilderFirm $tenant): RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $admin = $tenant->adminUser();
        if (! $admin) {
            return redirect()->route('tenants.index')->with('error', 'No admin user found for this tenant.');
        }
        $newPassword = Str::random(12);
        $admin->update(['password' => Hash::make($newPassword)]);
        return redirect()->route('tenants.index')
            ->with('show_password', true)
            ->with('password_tenant', $tenant->name)
            ->with('password_email', $admin->email)
            ->with('password_value', $newPassword)
            ->with('success', 'Password reset. Copy it now — it won’t be shown again.');
    }

    public function show(string $slug, DashboardService $dashboardService): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::with('plan')->where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $stats = $dashboardService->getTenantDashboardStats($builder);

        return view('dashboard', [
            'user' => $user,
            'tenant' => $builder,
            'stats' => $stats,
            'section' => null,
        ]);
    }

    /**
     * Show tenant dashboard with a specific section (projects, leads, etc.).
     * Same auth and data as show(), but keeps the URL and shows section-specific content.
     */
    public function showSection(string $slug, string $section, DashboardService $dashboardService, ReportService $reportService): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::with('plan')->where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $stats = $dashboardService->getTenantDashboardStats($builder);

        $data = [
            'user' => $user,
            'tenant' => $builder,
            'stats' => $stats,
            'section' => $section,
        ];

        if ($section === 'projects') {
            $data['projects'] = $builder->projects()->orderBy('name')->get();
        }
        if ($section === 'leads') {
            $data['leads'] = Lead::with(['project', 'customer', 'channelPartner.user'])
                ->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builder->id))
                ->orderByDesc('created_at')
                ->paginate(20);
        }
        if ($section === 'cp-applications') {
            $query = $builder->cpApplications()->with(['channelPartner.user', 'manager'])->latest();
            if (request()->filled('status')) {
                $query->where('status', request('status'));
            }
            $data['cpApplications'] = $query->paginate(20)->withQueryString();
            $data['managers'] = User::where('builder_firm_id', $builder->id)->where('role', User::ROLE_MANAGER)->where('is_active', true)->orderBy('name')->get();
        }
        if ($section === 'managers') {
            $data['managers'] = User::where('builder_firm_id', $builder->id)->where('role', User::ROLE_MANAGER)->orderBy('name')->get();
        }
        if ($section === 'forms') {
            $data['forms'] = $builder->forms()->with('formFields')->orderBy('type')->orderBy('name')->get();
        }
        if ($section === 'visits') {
            $data['visits'] = Visit::with(['lead.customer', 'lead.project', 'lead.channelPartner.user', 'visitOtp'])
                ->whereHas('lead.project', fn ($q) => $q->where('builder_firm_id', $builder->id))
                ->orderByDesc('scheduled_at')
                ->paginate(20);
            $data['canSendOtp'] = in_array($user->role ?? '', ['manager', 'sales_exec'], true);
        }
        if ($section === 'locks') {
            $data['projects'] = $builder->projects()->orderBy('name')->get();
            $filters = request()->only(['project_id']);
            $builderFirmId = $user->isSuperAdmin() ? null : (int) $builder->id;
            $data['locks'] = $reportService->locksReport($builderFirmId, $filters);
        }
        if ($section === 'reports') {
            $data['projects'] = $builder->projects()->orderBy('name')->get();
            $filters = request()->only(['date_from', 'date_to', 'project_id']);
            $builderFirmId = $user->isSuperAdmin() ? null : (int) $builder->id;
            $data['reportsLeads'] = $reportService->leadsReport($builderFirmId, $filters);
            $data['reportsLocks'] = $reportService->locksReport($builderFirmId, $filters);
            $data['reportsCpPerformance'] = $reportService->cpPerformanceReport($builderFirmId, $filters);
            $data['reportsConversion'] = $reportService->conversionReport($builderFirmId, $filters);
        }

        return view('dashboard', $data);
    }

    public function settingsUpdate(Request $request, string $slug): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $validated = $request->validate([
            'logo' => 'nullable|image|max:2048',
            'logo_url' => 'nullable|string|max:500',
            'primary_color' => 'nullable|string|max:50',
            'default_lock_days' => 'nullable|integer|min:1|max:365',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:100',
        ]);
        $settings = $builder->settings ?? [];
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('tenant-logos', 'public');
            $settings['logo_url'] = asset('storage/' . $path);
        } elseif (! empty($validated['logo_url'])) {
            $settings['logo_url'] = $validated['logo_url'];
        }
        if (array_key_exists('primary_color', $validated)) {
            $settings['primary_color'] = $validated['primary_color'] ?: null;
        }
        if (array_key_exists('mail_from_address', $validated)) {
            $settings['mail_from_address'] = $validated['mail_from_address'] ?: null;
        }
        if (array_key_exists('mail_from_name', $validated)) {
            $settings['mail_from_name'] = $validated['mail_from_name'] ?: null;
        }
        $update = [
            'settings' => $settings,
            'default_lock_days' => $validated['default_lock_days'] ?? $builder->default_lock_days ?? 30,
        ];
        $builder->update($update);
        return redirect()->route('tenant.settings', $slug)->with('success', 'Settings updated.');
    }

    public function projectEdit(string $slug, Project $project): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $project->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        return view('dashboard.project-edit', [
            'user' => $user,
            'tenant' => $builder,
            'project' => $project,
        ]);
    }

    public function projectStore(Request $request, string $slug): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::with('plan')->where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ($builder->projects()->count() >= $builder->getMaxProjects()) {
            return redirect()->route('tenant.projects.index', $slug)
                ->with('error', 'Project limit reached for your plan.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
        ]);
        $builder->projects()->create([
            'name' => $validated['name'],
            'location' => $validated['location'] ?? null,
            'status' => 'active',
        ]);
        return redirect()->route('tenant.projects.index', $slug)->with('success', 'Project created.');
    }

    public function projectUpdate(Request $request, string $slug, Project $project): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $project->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $request->merge([
            'lock_days_override' => $request->filled('lock_days_override') ? (int) $request->lock_days_override : null,
        ]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
            'lock_days_override' => 'nullable|integer|min:1|max:365',
        ]);
        $project->update($validated);
        return redirect()->route('tenant.projects.index', $slug)->with('success', 'Project updated.');
    }

    public function projectDestroy(string $slug, Project $project): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $project->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $project->delete();
        return redirect()->route('tenant.projects.index', $slug)->with('success', 'Project deleted.');
    }

    public function channelPartnerShow(string $slug, ChannelPartner $channelPartner, ReportService $reportService): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::with('plan')->where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403, 'You do not have access to this tenant.');
        }
        $cpApplication = CpApplication::where('builder_firm_id', $builder->id)
            ->where('channel_partner_id', $channelPartner->id)
            ->first();
        if (! $cpApplication) {
            abort(404, 'This channel partner has no application with this builder.');
        }
        $channelPartner->load('user');
        $leads = Lead::with(['customer', 'project'])
            ->where('channel_partner_id', $channelPartner->id)
            ->whereHas('project', fn ($q) => $q->where('builder_firm_id', $builder->id))
            ->orderByDesc('created_at')
            ->paginate(10);
        $ranking = $reportService->getCpVisitDoneRanking((int) $builder->id);
        $rankRow = $ranking->firstWhere('channel_partner_id', $channelPartner->id);
        $visitDoneCount = $rankRow ? $rankRow->visit_done_count : 0;
        $rank = $rankRow ? $rankRow->rank : null;

        $managers = User::where('builder_firm_id', $builder->id)->where('role', User::ROLE_MANAGER)->where('is_active', true)->orderBy('name')->get();
        return view('dashboard.cp_detail', [
            'user' => $user,
            'tenant' => $builder,
            'channelPartner' => $channelPartner,
            'cpApplication' => $cpApplication,
            'leads' => $leads,
            'visit_done_count' => $visitDoneCount,
            'rank' => $rank,
            'managers' => $managers,
        ]);
    }

    public function cpApplicationApprove(Request $request, string $slug, CpApplication $cpApplication): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $cpApplication->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        if ($cpApplication->status !== CpApplication::STATUS_PENDING) {
            return redirect()->route('tenant.cp-applications.index', ['slug' => $slug, 'status' => 'pending'])->with('error', 'Application already processed.');
        }
        $builder->load('plan');
        $approvedCount = CpApplication::where('builder_firm_id', $builder->id)->where('status', CpApplication::STATUS_APPROVED)->count();
        if ($approvedCount >= $builder->getMaxChannelPartners()) {
            return redirect()->route('tenant.cp-applications.index', ['slug' => $slug])->with('error', 'Channel partner limit reached for your plan.');
        }
        $cpApplication->update([
            'status' => CpApplication::STATUS_APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'notes' => null,
        ]);
        $cpUser = $cpApplication->channelPartner?->user;
        if ($cpUser && $cpUser->email) {
            $cpUser->notify(new CpApplicationApprovedNotification($builder, $cpApplication));
        }
        return redirect()->route('tenant.cp-applications.index', ['slug' => $slug, 'status' => 'pending'])->with('success', 'CP approved. They will now appear in the customer form dropdown.');
    }

    public function cpApplicationReject(Request $request, string $slug, CpApplication $cpApplication): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $cpApplication->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $validated = $request->validate(['notes' => 'required|string|max:1000']);
        $cpApplication->update([
            'status' => CpApplication::STATUS_REJECTED,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'notes' => $validated['notes'],
        ]);
        $cpUser = $cpApplication->channelPartner?->user;
        if ($cpUser && $cpUser->email) {
            $cpUser->notify(new CpApplicationRejectedNotification($builder, $cpApplication, $validated['notes']));
        }
        return redirect()->route('tenant.cp-applications.index', ['slug' => $slug])->with('success', 'CP rejected.');
    }

    public function managerStore(Request $request, string $slug): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::with('plan')->where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && ! $user->isBuilderAdmin()) {
            abort(403, 'Only Builder Admin or Super Admin can create managers.');
        }
        if ((int) $user->builder_firm_id !== (int) $builder->id && ! $user->isSuperAdmin()) {
            abort(403);
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $builder->load('plan');
        $managersCount = User::where('builder_firm_id', $builder->id)->where('role', User::ROLE_MANAGER)->count();
        $maxUsers = $builder->getMaxUsers();
        $currentUsers = User::where('builder_firm_id', $builder->id)->count();
        if ($currentUsers >= $maxUsers) {
            return redirect()->route('tenant.managers.index', $slug)->with('error', 'User limit reached for your plan.');
        }
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_MANAGER,
            'builder_firm_id' => $builder->id,
            'is_active' => true,
        ]);
        return redirect()->route('tenant.managers.index', $slug)->with('success', 'Manager created. They can log in with the given email and password.');
    }

    public function cpApplicationAssignManager(Request $request, string $slug, CpApplication $cpApplication): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $builder = BuilderFirm::where('slug', $slug)->firstOrFail();
        $user = session('user');
        if (! $user->isSuperAdmin() && (int) $user->builder_firm_id !== (int) $builder->id) {
            abort(403);
        }
        if ((int) $cpApplication->builder_firm_id !== (int) $builder->id) {
            abort(404);
        }
        $validated = $request->validate([
            'manager_id' => 'nullable|exists:users,id',
        ]);
        $managerId = $validated['manager_id'] ?? null;
        if ($managerId !== null) {
            $manager = User::find($managerId);
            if (! $manager || (int) $manager->builder_firm_id !== (int) $builder->id || $manager->role !== User::ROLE_MANAGER) {
                return redirect()->back()->with('error', 'Invalid manager.');
            }
        }
        $cpApplication->update(['manager_id' => $managerId]);
        return redirect()->back()->with('success', 'Manager assigned.');
    }
}
