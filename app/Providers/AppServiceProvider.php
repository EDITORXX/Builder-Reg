<?php

namespace App\Providers;

use App\Models\BuilderFirm;
use App\Models\LeadLock;
use App\View\Composers\NavComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('lock', fn ($value) => LeadLock::findOrFail($value));

        View::composer('layouts.app', function ($view) {
            $user = session('user');
            $data = $view->getData();
            $tenant = $data['tenant'] ?? null;
            if ($tenant === null && $user && $user->builder_firm_id) {
                $tenant = BuilderFirm::find($user->builder_firm_id);
            }
            $tenant_logo_url = null;
            $tenant_primary_color = null;
            if ($tenant) {
                $tenant_logo_url = $tenant->getLogoUrl();
                $tenant_primary_color = $tenant->getPrimaryColor();
            }
            $view->with(compact('tenant', 'tenant_logo_url', 'tenant_primary_color'));
        });

        View::composer('layouts.app', NavComposer::class);
    }
}
