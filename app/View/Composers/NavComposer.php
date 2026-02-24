<?php

namespace App\View\Composers;

use App\Models\BuilderFirm;
use App\Models\User;
use Illuminate\View\View;

class NavComposer
{
    public function compose(View $view): void
    {
        $user = session('user');
        $data = $view->getData();
        $tenant = $data['tenant'] ?? null;

        $context = $this->resolveContext($user, $tenant);
        $navItems = $this->buildNavItems($user, $context, $tenant);

        $view->with('navItems', $navItems);
    }

    private function resolveContext(?User $user, $tenant): string
    {
        if (! $user) {
            return 'global';
        }
        if ($user->role === 'channel_partner') {
            return 'channel_partner';
        }
        if ($tenant !== null) {
            return 'tenant';
        }
        return 'global';
    }

    private function buildNavItems(?User $user, string $context, $tenant): array
    {
        if (! $user) {
            return [];
        }

        $items = config('nav.items', []);
        $filtered = array_filter($items, function ($item) use ($user, $context) {
            if (($item['context'] ?? '') !== $context) {
                return false;
            }
            return in_array($user->role, $item['roles'] ?? [], true);
        });

        $result = [];
        $seenKeys = [];
        foreach ($filtered as $item) {
            $key = $item['key'];
            if (in_array($key, $seenKeys, true)) {
                continue;
            }
            $seenKeys[] = $key;
            $url = $this->resolveUrl($item, $tenant);
            if ($url === null) {
                continue;
            }
            $result[] = [
                'key' => $key,
                'label' => $item['label'],
                'url' => $url,
                'route' => $item['route'],
                'icon' => $item['icon'],
            ];
        }

        return $result;
    }

    private function resolveUrl(array $item, $tenant): ?string
    {
        $route = $item['route'] ?? null;
        if (! $route) {
            return null;
        }

        if (str_starts_with($route, 'tenant.') && $tenant instanceof BuilderFirm) {
            try {
                return route($route, ['slug' => $tenant->slug]);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return route($route);
        } catch (\Throwable) {
            return null;
        }
    }
}
