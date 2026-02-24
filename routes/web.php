<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CpDashboardController;
use App\Http\Controllers\Web\FormController;
use App\Http\Controllers\Web\PlanController;
use App\Http\Controllers\Web\PublicRegistrationController;
use App\Http\Controllers\Web\TenantController;
use App\Http\Controllers\Web\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register/cp/{builder_slug}', [PublicRegistrationController::class, 'showCpForm'])->name('register.cp');
Route::post('/register/cp/{builder_slug}', [PublicRegistrationController::class, 'submitCpForm']);
Route::get('/register/customer/{builder_slug}', [PublicRegistrationController::class, 'showCustomerForm'])->name('register.customer');
Route::post('/register/customer/{builder_slug}', [PublicRegistrationController::class, 'submitCustomerForm']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth_web')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/t/{slug}', [TenantController::class, 'show'])->name('tenant.dashboard');

    Route::get('/profile', function () {
        return redirect()->route('dashboard');
    })->name('profile.show');

    Route::get('/cp/dashboard', [CpDashboardController::class, 'dashboard'])->name('cp.dashboard');
    Route::get('/cp/leads', [CpDashboardController::class, 'leads'])->name('cp.leads');
    Route::get('/cp/my-applications', function () {
        return redirect()->route('cp.dashboard');
    })->name('cp.my-applications');

    Route::middleware('role_web:super_admin')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::post('/tenants/{tenant}/reset-admin-password', [TenantController::class, 'resetAdminPassword'])->name('tenants.reset-password');

        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    });

    Route::get('/t/{slug}/settings', [TenantController::class, 'showSection'])->defaults('section', 'settings')->name('tenant.settings');
    Route::put('/t/{slug}/settings', [TenantController::class, 'settingsUpdate'])->name('tenant.settings.update');
    Route::get('/t/{slug}/projects', [TenantController::class, 'showSection'])->defaults('section', 'projects')->name('tenant.projects.index');
    Route::get('/t/{slug}/projects/{project}/edit', [TenantController::class, 'projectEdit'])->name('tenant.projects.edit');
    Route::post('/t/{slug}/projects', [TenantController::class, 'projectStore'])->name('tenant.projects.store');
    Route::put('/t/{slug}/projects/{project}', [TenantController::class, 'projectUpdate'])->name('tenant.projects.update');
    Route::delete('/t/{slug}/projects/{project}', [TenantController::class, 'projectDestroy'])->name('tenant.projects.destroy');
    Route::get('/t/{slug}/leads', [TenantController::class, 'showSection'])->defaults('section', 'leads')->name('tenant.leads.index');
    Route::get('/t/{slug}/cp-applications', [TenantController::class, 'showSection'])->defaults('section', 'cp-applications')->name('tenant.cp-applications.index');
    Route::get('/t/{slug}/channel-partners/{channelPartner}', [TenantController::class, 'channelPartnerShow'])->name('tenant.channel-partners.show');
    Route::post('/t/{slug}/cp-applications/{cpApplication}/approve', [TenantController::class, 'cpApplicationApprove'])->name('tenant.cp-applications.approve');
    Route::post('/t/{slug}/cp-applications/{cpApplication}/reject', [TenantController::class, 'cpApplicationReject'])->name('tenant.cp-applications.reject');
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
