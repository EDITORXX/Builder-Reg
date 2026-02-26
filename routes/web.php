<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CpDashboardController;
use App\Http\Controllers\Web\FormController;
use App\Http\Controllers\Web\InstallController;
use App\Http\Controllers\Web\PlanController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\PublicRegistrationController;
use App\Http\Controllers\Web\SystemController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\VisitCheckinController;
use App\Http\Controllers\Web\VisitController;
use App\Http\Controllers\Web\VisitVerificationController;
use App\Http\Controllers\Web\CpVisitScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('redirect_if_installed')->group(function () {
    Route::get('/install', [InstallController::class, 'showForm'])->name('install.show');
    Route::post('/install', [InstallController::class, 'store'])->name('install.store');
    Route::get('/install/run', [InstallController::class, 'run'])->name('install.run');
});

Route::get('/register/cp/{builder_slug}', [PublicRegistrationController::class, 'showCpForm'])->name('register.cp');
Route::post('/register/cp/{builder_slug}', [PublicRegistrationController::class, 'submitCpForm']);
Route::get('/register/customer/{builder_slug}', [PublicRegistrationController::class, 'showCustomerForm'])->name('register.customer');
Route::post('/register/customer/{builder_slug}', [PublicRegistrationController::class, 'submitCustomerForm']);
Route::get('/register/customer/{builder_slug}/scan', [PublicRegistrationController::class, 'showCustomerScan'])->name('register.customer.scan');

Route::get('/visit/checkin/thanks', function () {
    return view('visit.checkin-thanks');
})->name('visit.checkin.thanks');
Route::get('/visit/checkin/{token}', [VisitCheckinController::class, 'show'])->name('visit.checkin');
Route::post('/visit/checkin/{token}', [VisitCheckinController::class, 'submit'])->name('visit.checkin.submit');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth_web')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/preferences/sidebar-mode', function (\Illuminate\Http\Request $request) {
        $mode = $request->input('mode', 'toggle');
        if ($mode === 'icon') {
            session(['sidebar_nav_icon_only' => true]);
        } elseif ($mode === 'text') {
            session(['sidebar_nav_icon_only' => false]);
        } else {
            session(['sidebar_nav_icon_only' => ! session('sidebar_nav_icon_only', false)]);
        }
        return redirect()->back();
    })->name('preferences.sidebar-mode');

    Route::get('/t/{slug}', [TenantController::class, 'show'])->name('tenant.dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    Route::get('/cp/dashboard', [CpDashboardController::class, 'dashboard'])->name('cp.dashboard');
    Route::get('/cp/leads', [CpDashboardController::class, 'leads'])->name('cp.leads');
    Route::get('/cp/my-applications', function () {
        return redirect()->route('cp.dashboard');
    })->name('cp.my-applications');
    Route::get('/cp/scheduled-visits', [CpVisitScheduleController::class, 'index'])->name('cp.scheduled-visits.index');
    Route::get('/cp/scheduled-visits/create', [CpVisitScheduleController::class, 'create'])->name('cp.scheduled-visits.create');
    Route::post('/cp/scheduled-visits', [CpVisitScheduleController::class, 'store'])->name('cp.scheduled-visits.store');
    Route::get('/cp/scheduled-visits/{visitSchedule}', [CpVisitScheduleController::class, 'show'])->name('cp.scheduled-visits.show');
    Route::get('/cp/direct-visit', [CpVisitScheduleController::class, 'directVisitForm'])->name('cp.direct-visit');
    Route::post('/cp/direct-visit', [CpVisitScheduleController::class, 'directVisitSubmit'])->name('cp.direct-visit.submit');

    Route::get('/t/{slug}/visit-verifications', [TenantController::class, 'showSection'])->defaults('section', 'visit-verifications')->name('tenant.visit-verifications.index');
    Route::post('/t/{slug}/visit-verifications/{lead}/approve', [VisitVerificationController::class, 'approve'])->name('tenant.visit-verifications.approve');
    Route::post('/t/{slug}/visit-verifications/{lead}/reject', [VisitVerificationController::class, 'reject'])->name('tenant.visit-verifications.reject');

    Route::middleware('role_web:super_admin')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{tenant}/reset-admin-password', [TenantController::class, 'resetAdminPassword'])->name('tenants.reset-password');

        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');

        Route::post('/system/git-push', [SystemController::class, 'gitPush'])->name('system.git-push');
        Route::post('/system/migrate', [SystemController::class, 'migrate'])->name('system.migrate');
    });

    Route::get('/t/{slug}/settings', [TenantController::class, 'showSection'])->defaults('section', 'settings')->name('tenant.settings');
    Route::put('/t/{slug}/settings', [TenantController::class, 'settingsUpdate'])->name('tenant.settings.update');
    Route::get('/t/{slug}/projects', [TenantController::class, 'showSection'])->defaults('section', 'projects')->name('tenant.projects.index');
    Route::get('/t/{slug}/projects/{project}/edit', [TenantController::class, 'projectEdit'])->name('tenant.projects.edit');
    Route::post('/t/{slug}/projects', [TenantController::class, 'projectStore'])->name('tenant.projects.store');
    Route::put('/t/{slug}/projects/{project}', [TenantController::class, 'projectUpdate'])->name('tenant.projects.update');
    Route::delete('/t/{slug}/projects/{project}', [TenantController::class, 'projectDestroy'])->name('tenant.projects.destroy');
    Route::get('/t/{slug}/leads', [TenantController::class, 'showSection'])->defaults('section', 'leads')->name('tenant.leads.index');
    Route::get('/t/{slug}/leads/{lead}', [TenantController::class, 'leadShow'])->name('tenant.leads.show');
    Route::post('/t/{slug}/leads/{lead}/sales-status', [TenantController::class, 'updateLeadSalesStatus'])->name('tenant.leads.update-sales-status');
    Route::get('/t/{slug}/cp-applications', [TenantController::class, 'showSection'])->defaults('section', 'cp-applications')->name('tenant.cp-applications.index');
    Route::get('/t/{slug}/channel-partners/{channelPartner}', [TenantController::class, 'channelPartnerShow'])->name('tenant.channel-partners.show');
    Route::post('/t/{slug}/channel-partners/{channelPartner}/reset-password', [TenantController::class, 'resetCpPassword'])->name('tenant.channel-partners.reset-password');
    Route::post('/t/{slug}/channel-partners/{channelPartner}/inactive', [TenantController::class, 'cpSetInactive'])->name('tenant.channel-partners.inactive');
    Route::delete('/t/{slug}/channel-partners/{channelPartner}', [TenantController::class, 'cpDelete'])->name('tenant.channel-partners.delete');
    Route::get('/t/{slug}/leads/{lead}/visit-photo', [TenantController::class, 'leadVisitPhoto'])->name('tenant.leads.visit-photo');
    Route::post('/t/{slug}/cp-applications/{cpApplication}/approve', [TenantController::class, 'cpApplicationApprove'])->name('tenant.cp-applications.approve');
    Route::post('/t/{slug}/cp-applications/{cpApplication}/reject', [TenantController::class, 'cpApplicationReject'])->name('tenant.cp-applications.reject');
    Route::post('/t/{slug}/cp-applications/{cpApplication}/assign-manager', [TenantController::class, 'cpApplicationAssignManager'])->name('tenant.cp-applications.assign-manager');
    Route::get('/t/{slug}/managers', [TenantController::class, 'showSection'])->defaults('section', 'managers')->name('tenant.managers.index');
    Route::post('/t/{slug}/managers', [TenantController::class, 'managerStore'])->name('tenant.managers.store');
    Route::get('/t/{slug}/managers/{manager}/edit', [TenantController::class, 'managerEdit'])->name('tenant.managers.edit');
    Route::put('/t/{slug}/managers/{manager}', [TenantController::class, 'managerUpdate'])->name('tenant.managers.update');
    Route::post('/t/{slug}/managers/{manager}/reset-password', [TenantController::class, 'resetManagerPassword'])->name('tenant.managers.reset-password');
    Route::get('/t/{slug}/forms', [TenantController::class, 'showSection'])->defaults('section', 'forms')->name('tenant.forms.index');
    Route::get('/t/{slug}/forms/create', [FormController::class, 'create'])->name('tenant.forms.create');
    Route::post('/t/{slug}/forms', [FormController::class, 'store'])->name('tenant.forms.store');
    Route::post('/t/{slug}/forms/from-template', [FormController::class, 'createFromTemplate'])->name('tenant.forms.from-template');
    Route::get('/t/{slug}/forms/{form}/edit', [FormController::class, 'edit'])->name('tenant.forms.edit');
    Route::put('/t/{slug}/forms/{form}', [FormController::class, 'update'])->name('tenant.forms.update');
    Route::delete('/t/{slug}/forms/{form}', [FormController::class, 'destroy'])->name('tenant.forms.destroy');
    Route::post('/t/{slug}/forms/{form}/activate', [FormController::class, 'setActive'])->name('tenant.forms.activate');
    Route::post('/t/{slug}/forms/{form}/fields', [FormController::class, 'storeField'])->name('tenant.forms.fields.store');
    Route::put('/t/{slug}/forms/{form}/fields/{formField}', [FormController::class, 'updateField'])->name('tenant.forms.fields.update');
    Route::delete('/t/{slug}/forms/{form}/fields/{formField}', [FormController::class, 'destroyField'])->name('tenant.forms.fields.destroy');
    Route::get('/t/{slug}/locks', [TenantController::class, 'showSection'])->defaults('section', 'locks')->name('tenant.locks.index');
    Route::get('/t/{slug}/visits', [TenantController::class, 'showSection'])->defaults('section', 'visits')->name('tenant.visits.index');
    Route::patch('/t/{slug}/visits/{visit}/reschedule', [VisitController::class, 'reschedule'])->name('tenant.visits.reschedule');
    Route::post('/t/{slug}/visits/{visit}/cancel', [VisitController::class, 'cancel'])->name('tenant.visits.cancel');
    Route::post('/t/{slug}/visits/{visit}/confirm', [VisitController::class, 'confirm'])->name('tenant.visits.confirm');
    Route::post('/t/{slug}/visits/{visit}/otp/send', [VisitController::class, 'sendOtp'])->name('tenant.visits.otp.send');
    Route::post('/t/{slug}/visits/{visit}/otp/verify', [VisitController::class, 'verifyOtp'])->name('tenant.visits.otp.verify');
    Route::get('/t/{slug}/reports', [TenantController::class, 'showSection'])->defaults('section', 'reports')->name('tenant.reports.index');
    Route::get('/t/{slug}/profile', [TenantController::class, 'showSection'])->defaults('section', 'profile')->name('tenant.profile');
});
