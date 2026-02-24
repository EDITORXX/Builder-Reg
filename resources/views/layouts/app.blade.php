<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @if(!empty($tenant_primary_color))
    <style>
        :root { --tenant-primary: {{ $tenant_primary_color }}; }
        .sidebar-brand-icon { background: var(--tenant-primary) !important; }
        .btn-primary { background: var(--tenant-primary) !important; }
        .btn-primary:hover { filter: brightness(0.9); }
    </style>
    @endif
</head>
<body class="app-body">
    <div class="app-shell">
        @php($navIconOnly = session('sidebar_nav_icon_only', false))
        <aside class="sidebar {{ $navIconOnly ? 'sidebar--icon-only' : '' }}">
            <div class="sidebar-brand">
                @if(!empty($tenant_logo_url))
                    <img src="{{ $tenant_logo_url }}" alt="Logo" class="sidebar-brand-icon" style="width: 40px; height: 40px; object-fit: contain; border-radius: var(--radius);">
                @else
                    <span class="sidebar-brand-icon">BP</span>
                @endif
                <span class="sidebar-brand-text">{{ optional($tenant)->name ?? 'Builder Partner' }}</span>
            </div>
            <nav class="sidebar-nav">
                @foreach($navItems ?? [] as $item)
                <a href="{{ $item['url'] }}" class="sidebar-link {{ request()->routeIs($item['route']) ? 'sidebar-link-active' : '' }}" title="{{ $item['label'] }}">
                    @include('partials.sidebar-icon', ['name' => $item['icon']])
                    <span class="sidebar-link-text">{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>
            <div class="sidebar-footer">
                <form method="POST" action="{{ route('preferences.sidebar-mode') }}" class="sidebar-mode-form">
                    @csrf
                    <button type="submit" class="sidebar-link sidebar-link-toggle" title="{{ $navIconOnly ? 'Show text' : 'Icons only' }}">
                        @if($navIconOnly)
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                            <span class="sidebar-link-text">Show text</span>
                        @else
                            <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            <span class="sidebar-link-text">Icons only</span>
                        @endif
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-link sidebar-link-logout" title="Log out">
                        <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span class="sidebar-link-text">Log out</span>
                    </button>
                </form>
            </div>
        </aside>
        <main class="main">
            <header class="topbar">
                <div class="topbar-left">
                    <h1 class="topbar-title">@yield('heading', 'Dashboard')</h1>
                    <p class="topbar-subtitle">@yield('subtitle', '')</p>
                </div>
                <a href="{{ url('/') }}" class="topbar-home">Home</a>
            </header>
            <div class="main-content">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
