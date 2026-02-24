<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => __('The provided credentials are incorrect.')]);
        }
        if (! $user->is_active) {
            throw ValidationException::withMessages(['email' => __('Account is deactivated.')]);
        }

        $user->tokens()->where('name', 'api')->delete();
        $token = $user->createToken('api')->plainTextToken;

        session([
            'api_token' => $token,
            'user' => $user->load('builderFirm', 'channelPartner'),
        ]);

        if ($user->isChannelPartner()) {
            return redirect()->intended(route('cp.dashboard'));
        }
        if ($user->builder_firm_id) {
            $builder = $user->builderFirm;
            if ($builder) {
                return redirect()->intended(url("/t/{$builder->slug}"));
            }
        }
        return redirect()->intended(route('dashboard'));
    }

    public function dashboard(Request $request, DashboardService $dashboardService): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }

        $user = session('user');
        if (! $user->isSuperAdmin()) {
            if ($user->builder_firm_id) {
                if (! $user->relationLoaded('builderFirm')) {
                    $user->load('builderFirm');
                }
                $builder = $user->builderFirm;
                if ($builder) {
                    return redirect()->route('tenant.dashboard', ['slug' => $builder->slug]);
                }
            }
            if ($user->isChannelPartner()) {
                return redirect()->route('cp.dashboard');
            }
            return redirect()->route('profile.show');
        }

        $stats = $dashboardService->getSuperAdminStats();

        return view('dashboard', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        session()->forget(['api_token', 'user', 'sidebar_nav_icon_only']);
        return redirect()->route('login');
    }
}
