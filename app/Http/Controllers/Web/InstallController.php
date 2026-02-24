<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InstallController extends Controller
{
    private function installedPath(): string
    {
        return storage_path('app/installed');
    }

    private function tokenPath(): string
    {
        return storage_path('app/install_token');
    }

    private function payloadPath(): string
    {
        return storage_path('app/install_data.json');
    }

    public function showForm(): View|RedirectResponse
    {
        if (file_exists($this->installedPath())) {
            return redirect()->route('login');
        }

        return view('install');
    }

    public function store(Request $request): RedirectResponse
    {
        if (file_exists($this->installedPath())) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_url' => 'required|url|max:255',
            'db_host' => 'required|string|max:255',
            'db_port' => 'nullable|string|max:10',
            'db_database' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
            'mail_mailer' => 'required|in:smtp,log',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:10',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|string|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'admin_name' => 'nullable|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ], [
            'admin_password.min' => 'Password must be at least 8 characters.',
            'admin_password.confirmed' => 'Password confirmation does not match.',
        ]);

        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');
        $content = file_exists($envPath) ? file_get_contents($envPath) : (file_exists($examplePath) ? file_get_contents($examplePath) : '');

        $replacements = [
            'APP_NAME' => $this->envValue($validated['app_name']),
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($validated['app_url'], '/'),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $this->envValue($validated['db_host']),
            'DB_PORT' => $this->envValue($validated['db_port'] ?? '3306'),
            'DB_DATABASE' => $this->envValue($validated['db_database']),
            'DB_USERNAME' => $this->envValue($validated['db_username']),
            'DB_PASSWORD' => $this->envValue($validated['db_password'] ?? ''),
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
            'MAIL_MAILER' => $validated['mail_mailer'],
            'MAIL_HOST' => $this->envValue($validated['mail_host'] ?? '127.0.0.1'),
            'MAIL_PORT' => $this->envValue($validated['mail_port'] ?? '2525'),
            'MAIL_USERNAME' => $this->envValue($validated['mail_username'] ?? ''),
            'MAIL_PASSWORD' => $this->envValue($validated['mail_password'] ?? ''),
            'MAIL_FROM_ADDRESS' => $this->envValue($validated['mail_from_address'] ?? 'hello@example.com'),
            'MAIL_FROM_NAME' => $this->envValue($validated['mail_from_name'] ?? $validated['app_name']),
        ];

        foreach ($replacements as $key => $value) {
            $content = $this->setEnvVariable($content, $key, $value);
        }

        if (! preg_match('/^\s*APP_KEY=/m', $content)) {
            $content = "APP_KEY=\n" . $content;
        }

        file_put_contents($envPath, $content);

        $token = Str::random(64);
        file_put_contents($this->tokenPath(), $token);

        $payload = [
            'admin_name' => $validated['admin_name'] ?? 'Super Admin',
            'admin_email' => $validated['admin_email'],
            'admin_password' => $validated['admin_password'],
        ];
        file_put_contents($this->payloadPath(), json_encode($payload));

        return redirect()->to(url('/install/run?token=' . $token));
    }

    public function run(Request $request): RedirectResponse
    {
        if (file_exists($this->installedPath())) {
            return redirect()->route('login');
        }

        $token = $request->query('token');
        if (! $token || ! file_exists($this->tokenPath()) || trim(file_get_contents($this->tokenPath())) !== $token) {
            abort(404, 'Invalid or expired install token.');
        }

        if (! file_exists($this->payloadPath())) {
            abort(404, 'Install data missing.');
        }

        $payload = json_decode(file_get_contents($this->payloadPath()), true);
        if (! $payload || empty($payload['admin_email']) || empty($payload['admin_password'])) {
            abort(404, 'Invalid install data.');
        }

        Artisan::call('key:generate', ['--force' => true]);
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PlanSeeder', '--force' => true]);

        User::create([
            'name' => $payload['admin_name'] ?? 'Super Admin',
            'email' => $payload['admin_email'],
            'password' => Hash::make($payload['admin_password']),
            'role' => User::ROLE_SUPER_ADMIN,
            'builder_firm_id' => null,
            'is_active' => true,
        ]);

        file_put_contents($this->installedPath(), '1');
        @unlink($this->tokenPath());
        @unlink($this->payloadPath());

        return redirect()->route('login')->with('success', 'Installation complete. Log in with your Super Admin account.');
    }

    private function envValue(string $value): string
    {
        if ($value === '' || preg_match('/[\s#\$]/', $value)) {
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }
        return $value;
    }

    private function setEnvVariable(string $content, string $key, string $value): string
    {
        $line = $key . '=' . $value;
        $pattern = '/^\s*#?\s*' . preg_quote($key, '/') . '\s*=.*$/m';
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $line, $content);
        } else {
            $content .= "\n" . $line;
        }
        return $content;
    }
}
