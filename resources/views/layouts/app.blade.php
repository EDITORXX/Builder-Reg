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
<body class="app-body" id="app-body">
    <div class="sidebar-overlay" id="sidebar-overlay" aria-hidden="true"></div>
    <div class="app-shell">
        @php($navIconOnly = session('sidebar_nav_icon_only', false))
        <aside class="sidebar {{ $navIconOnly ? 'sidebar--icon-only' : '' }}" id="sidebar">
            <div class="sidebar-brand">
                @if(!empty($tenant_logo_url))
                    <img src="{{ $tenant_logo_url }}" alt="Logo" class="sidebar-brand-icon" style="width: 40px; height: 40px; object-fit: contain; border-radius: var(--radius);">
                @else
                    <span class="sidebar-brand-icon">BP</span>
                @endif
                <span class="sidebar-brand-text">{{ optional($tenant)->name ?? 'Builder Partner' }}</span>
            </div>
            @if(isset($navUser) && $navUser)
            <div class="sidebar-user" style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); font-size: 0.875rem;">
                <div style="font-weight: 600; color: var(--text-primary);">{{ $navUser->name }}</div>
                <div style="color: var(--text-secondary); margin-top: 0.125rem;">{{ $navUser->getRoleLabel() }}</div>
            </div>
            @endif
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
                <button type="button" class="topbar-menu-btn" id="topbar-menu-btn" aria-label="Open menu" aria-expanded="false">
                    <svg class="topbar-menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
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
        @if(isset($navItems) && count($navItems) > 0)
        <nav class="bottom-nav" aria-label="Main">
            @foreach($navItems as $item)
            <a href="{{ $item['url'] }}" class="bottom-nav-link {{ request()->routeIs($item['route']) ? 'bottom-nav-link-active' : '' }}" title="{{ $item['label'] }}">
                @include('partials.sidebar-icon', ['name' => $item['icon']])
                <span class="bottom-nav-label">{{ $item['label'] }}</span>
            </a>
            @endforeach
            <button type="button" class="bottom-nav-link" id="bottom-nav-menu-btn" aria-label="Menu" title="Menu (Log out, Icons only)">
                <svg class="sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                <span class="bottom-nav-label">Menu</span>
            </button>
        </nav>
        @endif
    </div>
    <script>
(function () {
    var body = document.getElementById('app-body');
    var btn = document.getElementById('topbar-menu-btn');
    var overlay = document.getElementById('sidebar-overlay');
    var sidebar = document.getElementById('sidebar');
    if (!body || !btn || !overlay) return;
    function openSidebar() {
        body.classList.add('sidebar-open');
        btn.setAttribute('aria-expanded', 'true');
        btn.setAttribute('aria-label', 'Close menu');
        overlay.setAttribute('aria-hidden', 'false');
    }
    function closeSidebar() {
        body.classList.remove('sidebar-open');
        btn.setAttribute('aria-expanded', 'false');
        btn.setAttribute('aria-label', 'Open menu');
        overlay.setAttribute('aria-hidden', 'true');
    }
    btn.addEventListener('click', function () {
        if (body.classList.contains('sidebar-open')) closeSidebar(); else openSidebar();
    });
    overlay.addEventListener('click', closeSidebar);
    if (sidebar) {
        sidebar.addEventListener('click', function (e) {
            if (e.target.closest('a') || e.target.closest('button')) closeSidebar();
        });
    }
    var bottomMenuBtn = document.getElementById('bottom-nav-menu-btn');
    if (bottomMenuBtn && btn) {
        bottomMenuBtn.addEventListener('click', function () { btn.click(); });
    }
})();
    </script>
</body>
</html>
