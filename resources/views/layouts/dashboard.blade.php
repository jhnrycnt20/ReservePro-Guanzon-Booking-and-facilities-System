<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
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
                <img class="rp-brand-logo" src="{{ asset('images/guanzon_logo_green.png') }}" alt="Guanzon Resort">
                <div>
                    <div class="rp-brand-name">Guanzon</div>
                    <div class="rp-brand-role">@yield('role_label', 'Dashboard')</div>
                </div>
            </div>
            <nav class="rp-nav">
                @yield('sidebar')
            </nav>
            <div class="rp-sidebar-footer">
                <a href="{{ route('profile.edit') }}" class="rp-sidebar-user">
                    <div class="rp-sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="rp-sidebar-user-email">{{ auth()->user()->email }}</div>
                </a>
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
                    @php
                        $rpUnreadCount = auth()->user()->unreadNotifications()->count();
                        $rpRecentNotifications = auth()->user()->notifications()->latest()->limit(6)->get();
                    @endphp
                    <div class="rp-notif" data-rp-notif>
                        <button
                            type="button"
                            class="rp-icon-btn position-relative"
                            data-rp-notif-toggle
                            aria-label="Notifications"
                            aria-expanded="false"
                            aria-controls="rpNotifPanel"
                            title="Notifications"
                        >
                            <i class="bi bi-bell"></i>
                            @if($rpUnreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" data-rp-notif-badge>
                                    {{ $rpUnreadCount > 9 ? '9+' : $rpUnreadCount }}
                                </span>
                            @endif
                        </button>
                        <div class="rp-notif-panel" id="rpNotifPanel" data-rp-notif-panel hidden>
                            <div class="rp-notif-panel-head">
                                <strong>Notifications</strong>
                                @if($rpUnreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.read_all') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-rp-soft">Mark all read</button>
                                    </form>
                                @endif
                            </div>
                            <div class="rp-notif-panel-list">
                                @forelse($rpRecentNotifications as $notification)
                                    <a href="{{ route('notifications.open', $notification->id) }}" class="rp-notif-item {{ $notification->read_at ? '' : 'is-unread' }}">
                                        <div class="rp-notif-item-text">{{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}</div>
                                        <div class="rp-notif-item-meta">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            <span class="rp-notif-item-go">Open</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="rp-notif-empty">No notifications yet.</div>
                                @endforelse
                            </div>
                            <a href="{{ route('notifications.index') }}" class="rp-notif-panel-foot">View all</a>
                        </div>
                    </div>
                </div>
            </header>

            <main class="rp-content">
                @yield('content')
            </main>
        </div>
    </div>

    <div class="rp-sidebar-backdrop d-lg-none" id="sidebarBackdrop"></div>

    @include('partials.terms-modal')
    @include('partials.confirm-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    @stack('scripts')
</body>
</html>
