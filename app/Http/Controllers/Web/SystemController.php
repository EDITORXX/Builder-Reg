<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class SystemController extends Controller
{
    public function gitPush(Request $request): RedirectResponse
    {
        $user = session('user');
        if (! $user || ($user->role ?? '') !== 'super_admin') {
            abort(403, 'Only Super Admin can run system actions.');
        }
        if (! config('app.system_actions_enabled', true)) {
            return redirect()->route('dashboard')->with('error', 'System actions are disabled.');
        }

        $message = $request->input('message', 'Update from SaaS admin');
        $cwd = base_path();

        $result = Process::path($cwd)
            ->run('git add -A && git commit -m ' . escapeshellarg($message) . ' && git push');

        $output = trim($result->output() . "\n" . $result->errorOutput());
        if ($result->successful()) {
            return redirect()->route('dashboard')->with('success', 'Git push completed. ' . ($output ? "\n" . $output : ''));
        }

        return redirect()->route('dashboard')->with('error', 'Git push failed. ' . ($output ? "\n" . $output : $result->errorOutput() ?: 'Unknown error.'));
    }

    public function migrate(Request $request): RedirectResponse
    {
        $user = session('user');
        if (! $user || ($user->role ?? '') !== 'super_admin') {
            abort(403, 'Only Super Admin can run system actions.');
        }
        if (! config('app.system_actions_enabled', true)) {
            return redirect()->route('dashboard')->with('error', 'System actions are disabled.');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());
            return redirect()->route('dashboard')->with('success', 'Migrations completed. ' . ($output ? "\n" . $output : ''));
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')->with('error', 'Migrations failed: ' . $e->getMessage());
        }
    }
}
