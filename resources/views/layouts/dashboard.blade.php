<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'ReservePro') }}</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}?v={{ file_exists(public_path('css/reservepro.css')) ? filemtime(public_path('css/reservepro.css')) : '1' }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="rp-body theme-{{ trim($__env->yieldContent('theme', 'guest')) }}">
    <div class="rp-shell">
        <aside class="rp-sidebar" id="rpSidebar">
            <div class="rp-brand">
                <span class="rp-brand-mark"><i class="bi bi-palm"></i></span>
                <div>
                    <div class="rp-brand-name">ReservePro</div>
                    <div class="rp-brand-role">@yield('role_label', 'Dashboard')</div>
                </div>
            </div>
            <nav class="rp-nav">
                @yield('sidebar')
            </nav>
            <div class="rp-sidebar-footer">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="rp-main">
            <header class="rp-topbar">
                <button class="btn btn-light d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle menu">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <h1 class="rp-page-title mb-0">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('page_subtitle')
                        <p class="rp-page-subtitle mb-0">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="rp-topbar-actions ms-auto d-flex align-items-center gap-3">
                    <a href="{{ route('notifications.index') }}" class="rp-icon-btn position-relative" title="Notifications">
                        <i class="bi bi-bell"></i>
                        @if(auth()->user()->unreadNotifications()->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ auth()->user()->unreadNotifications()->count() > 9 ? '9+' : auth()->user()->unreadNotifications()->count() }}
                            </span>
                        @endif
                    </a>
                    <div class="text-end d-none d-md-block">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">{{ auth()->user()->email }}</div>
                    </div>
                </div>
            </header>

            <main class="rp-content">
                @include('partials.alerts')
                @yield('content')
            </main>
        </div>
    </div>

    <div class="rp-sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}"></script>
    @stack('scripts')
</body>
</html>
