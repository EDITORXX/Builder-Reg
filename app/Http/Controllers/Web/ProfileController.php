<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /** Profile page for any logged-in user (SaaS Admin, Builder Admin, Manager, CP, etc.). */
    public function show(): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }

        $user = session('user');
        if (! $user->relationLoaded('channelPartner')) {
            $user->load('channelPartner');
        }
        if (! $user->relationLoaded('builderFirm')) {
            $user->load('builderFirm');
        }

        return view('profile.show', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }

        $user = session('user');
        $user->refresh();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($validated);
        session(['user' => $user->load('builderFirm', 'channelPartner')]);

        return redirect()->route('profile.show')->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }

        $user = session('user');
        $user->refresh();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);
        session(['user' => $user->load('builderFirm', 'channelPartner')]);

        return redirect()->route('profile.show')->with('success', 'Password updated.');
    }
}
