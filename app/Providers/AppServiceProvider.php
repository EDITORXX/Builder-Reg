<?php

namespace App\Providers;

use App\Models\LeadLock;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Route::bind('lock', fn ($value) => LeadLock::findOrFail($value));
    }
}
