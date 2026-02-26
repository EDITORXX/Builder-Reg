<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CpDashboardController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $user = session('user');
        if (! $user->isChannelPartner()) {
            return redirect()->route('login');
        }
        $cp = $user->channelPartner;
        $leadsCount = $cp ? Lead::where('channel_partner_id', $cp->id)->count() : 0;
        return view('cp.dashboard', [
            'user' => $user,
            'tenant' => null,
            'leadsCount' => $leadsCount,
        ]);
    }

    public function leads(Request $request): View|RedirectResponse
    {
        if (! session('api_token') || ! session('user')) {
            return redirect()->route('login');
        }
        $user = session('user');
        if (! $user->isChannelPartner()) {
            return redirect()->route('login');
        }
        $cp = $user->channelPartner;
        $leads = $cp
            ? Lead::with(['project.builderFirm', 'customer'])->where('channel_partner_id', $cp->id)->orderByDesc('created_at')->paginate(20)
            : collect()->paginate(20);
        return view('cp.leads', [
            'user' => $user,
            'tenant' => null,
            'leads' => $leads,
        ]);
    }
}
