<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Builder Partner Platform — Lead & Channel Partner Management</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { font-family: 'Instrument Sans', system-ui, sans-serif; margin: 0; }
        .hero { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; color: #f8fafc; }
        .hero-title { font-size: clamp(1.75rem, 4vw, 2.5rem); font-weight: 700; margin: 0 0 0.5rem 0; letter-spacing: -0.02em; }
        .hero-sub { font-size: 1rem; opacity: 0.9; margin: 0 0 2rem 0; max-width: 480px; text-align: center; line-height: 1.5; }
        .hero-cta { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; font-size: 1rem; font-weight: 600; color: #fff; background: #2563eb; border: none; border-radius: 0.5rem; text-decoration: none; font-family: inherit; cursor: pointer; transition: filter 0.2s; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4); }
        .hero-cta:hover { filter: brightness(1.1); color: #fff; }
        .features { max-width: 900px; margin: 0 auto; padding: 3rem 1.5rem; }
        .features h2 { font-size: 1.25rem; font-weight: 600; color: #0f172a; margin: 0 0 1.5rem 0; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
        .feature-card { padding: 1rem 1.25rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; font-size: 0.875rem; color: #334155; line-height: 1.5; }
        .feature-card strong { color: #0f172a; display: block; margin-bottom: 0.25rem; }
        .footer-cta { text-align: center; padding: 2rem; background: #f1f5f9; }
        .footer-cta .btn { display: inline-block; padding: 0.625rem 1.25rem; font-size: 0.9375rem; font-weight: 600; color: #fff; background: #2563eb; border-radius: 0.5rem; text-decoration: none; }
        .footer-cta .btn:hover { filter: brightness(1.1); color: #fff; }
    </style>
</head>
<body>
    <section class="hero">
        <h1 class="hero-title">Builder Partner Platform</h1>
        <p class="hero-sub">Lead lock & channel partner management for builders. One place for projects, leads, CPs, site visits, and reports.</p>
        @if(Route::has('login'))
            @if(session('api_token'))
                <a href="{{ route('dashboard') }}" class="hero-cta">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" style="margin-top: 1rem;">
                    @csrf
                    <button type="submit" style="background: none; border: none; color: rgba(255,255,255,0.8); font-size: 0.875rem; cursor: pointer; text-decoration: underline;">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hero-cta">Log in</a>
            @endif
        @endif
    </section>

    <section class="features">
        <h2>Features</h2>
        <div class="features-grid">
            <div class="feature-card"><strong>Multi-tenant</strong> Super Admin can create and manage multiple builder tenants with plans and limits.</div>
            <div class="feature-card"><strong>Plans</strong> Basic, Growth, Enterprise with configurable limits (users, projects, CPs, leads).</div>
            <div class="feature-card"><strong>Projects</strong> Each builder manages projects; leads are linked to projects.</div>
            <div class="feature-card"><strong>Leads</strong> Track leads by status, source, budget; assign to channel partners or internal team.</div>
            <div class="feature-card"><strong>Channel Partners</strong> CPs apply per builder; approve/reject. View each CP’s details, leads, and ranking by site visits.</div>
            <div class="feature-card"><strong>Forms</strong> CP Registration and Customer Registration forms with templates; set active form and share public links.</div>
            <div class="feature-card"><strong>Lead locks</strong> Time-bound locks after site visit confirmation to protect CP attribution.</div>
            <div class="feature-card"><strong>Site visits</strong> Schedule, reschedule, cancel; confirm via OTP, manual, or QR. Track visit status.</div>
            <div class="feature-card"><strong>Reports</strong> Leads by project and status; CP performance; conversion (visit done → booked); active locks.</div>
            <div class="feature-card"><strong>Settings</strong> Builder logo and primary colour for branding.</div>
            <div class="feature-card"><strong>Profile</strong> Account details and builder info for each role.</div>
            <div class="feature-card"><strong>Roles</strong> Super Admin, Builder Admin, Manager, Sales Exec, Viewer, Channel Partner with role-based access.</div>
            <div class="feature-card"><strong>Public registration</strong> CP and Customer can register via builder-specific public URLs.</div>
            <div class="feature-card"><strong>Install wizard</strong> One-time /install setup: App URL, database, mail, and first Super Admin account.</div>
        </div>
    </section>

    <section class="footer-cta">
        @if(Route::has('login') && !session('api_token'))
            <a href="{{ route('login') }}" class="btn">Log in to dashboard</a>
        @endif
    </section>
</body>
</html>
