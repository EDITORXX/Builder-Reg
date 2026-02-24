<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    private function requireSuperAdmin(): ?RedirectResponse
    {
        $user = session('user');
        if (! session('api_token') || ! $user) {
            return redirect()->route('login');
        }
        if (! $user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can view plans.');
        }
        return null;
    }

    public function index(): View|RedirectResponse
    {
        $redirect = $this->requireSuperAdmin();
        if ($redirect) {
            return $redirect;
        }
        $plans = Plan::orderBy('max_users')->get();
        return view('plans.index', ['plans' => $plans]);
    }
}
