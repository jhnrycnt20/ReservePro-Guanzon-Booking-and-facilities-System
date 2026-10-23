<!DOCTYPE html>
<html lang="en">
<head>
    @php
        $rpStaffPanel = in_array(trim($__env->yieldContent('theme', 'guest')), ['admin', 'front_desk', 'security'], true);
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($rpStaffPanel)
        <script>
        try {
            document.documentElement.dataset.bsTheme = localStorage.getItem('rp-staff-theme') === 'dark' ? 'dark' : 'light';
        } catch (_) {
            document.documentElement.dataset.bsTheme = 'light';
        }
        </script>
    @endif
    <title>@yield('title', 'Dashboard') — {{ config('app.name', 'ReservePro') }}</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}?v={{ file_exists(public_path('css/reservepro.css')) ? filemtime(public_path('css/reservepro.css')) : '1' }}" rel="stylesheet">
    @if($rpStaffPanel)
        <link href="{{ asset('css/staff-dashboard.css') }}?v={{ filemtime(public_path('css/staff-dashboard.css')) }}" rel="stylesheet">
        <link href="{{ asset('css/staff-theme.css') }}?v={{ filemtime(public_path('css/staff-theme.css')) }}" rel="stylesheet">
    @endif
    @stack('styles')
</head>
<body class="rp-body theme-{{ trim($__env->yieldContent('theme', 'guest')) }} {{ $rpStaffPanel ? 'rp-staff-ui' : '' }}">
    <div class="rp-shell">
        <aside class="rp-sidebar" id="rpSidebar">
            <div class="rp-brand">
                <img class="rp-brand-logo" src="{{ asset('images/guanzon_logo_green.png') }}?v={{ filemtime(public_path('images/guanzon_logo_green.png')) }}" alt="Guanzon Resort">
                <div>
                    <div class="rp-brand-name">Guanzon</div>
                    <div class="rp-brand-role">@yield('role_label', 'Dashboard')</div>
                </div>
            </div>
            <nav class="rp-nav">
                @yield('sidebar')
            </nav>
            <div class="rp-sidebar-footer">
                @if($rpStaffPanel)
                    <div class="rp-sidebar-user">
                @else
                    <a href="{{ route('profile.edit') }}" class="rp-sidebar-user">
                @endif
                    <div class="rp-sidebar-user-avatar" aria-hidden="true">
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="">
                        @else
                            <span>{{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="rp-sidebar-user-text">
                        <div class="rp-sidebar-user-name">{{ auth()->user()->name }}</div>
                        <div class="rp-sidebar-user-email">{{ auth()->user()->email }}</div>
                    </div>
                @if($rpStaffPanel)
                    </div>
                @else
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100 rp-logout-btn">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <div class="rp-main">
            <header class="rp-topbar">
                <button class="btn btn-light {{ $rpStaffPanel ? 'rp-staff-sidebar-toggle' : 'd-lg-none' }}" type="button" id="sidebarToggle" aria-label="Toggle sidebar" aria-controls="rpSidebar" aria-expanded="false" title="Toggle sidebar">
                    @if($rpStaffPanel)
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M9 3v18"></path></svg>
                    @else
                        <i class="bi bi-list" aria-hidden="true"></i>
                    @endif
                </button>
                <div>
                    <h1 class="rp-page-title mb-0">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('page_subtitle')
                        <p class="rp-page-subtitle mb-0">@yield('page_subtitle')</p>
                    @endif
                </div>
                <div class="rp-topbar-actions ms-auto d-flex align-items-center gap-3">
                    @if($rpStaffPanel)
                        <button type="button" class="rp-theme-switch" data-rp-theme-toggle role="switch" aria-checked="false" aria-label="Switch to dark mode" title="Switch to dark mode">
                            <span class="rp-theme-switch-thumb" aria-hidden="true">
                                <i class="bi bi-moon-fill rp-theme-moon"></i>
                                <i class="bi bi-sun-fill rp-theme-sun"></i>
                            </span>
                        </button>
                    @endif
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
                    @if($rpStaffPanel)
                        <div class="dropdown rp-staff-account">
                            <button type="button" class="rp-topbar-avatar" id="rpStaffAccountToggle" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="Open account menu" title="Account menu">
                                @if(auth()->user()->avatar_url)
                                    <img src="{{ auth()->user()->avatar_url }}" alt="">
                                @else
                                    {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end rp-staff-account-menu" aria-labelledby="rpStaffAccountToggle">
                                <div class="rp-staff-account-summary">
                                    <strong>{{ auth()->user()->name }}</strong>
                                    <span>{{ auth()->user()->email }}</span>
                                </div>
                                <div class="dropdown-divider"></div>
                                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#rpProfileModal">
                                    <i class="bi bi-person-circle" aria-hidden="true"></i> Manage Profile
                                </button>
                                @if(auth()->user()->hasRole('admin'))
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#rpSettingsModal">
                                        <i class="bi bi-gear" aria-hidden="true"></i> Settings
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
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
    @include('partials.notice-modal')
    @include('partials.modals.profile-modal')
    @if(auth()->user()?->hasRole('admin'))
        @include('partials.modals.settings-modal')
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const successPopup = @json(session('success_popup'));
        if (successPopup && window.bootstrap) {
            const noticeEl = document.getElementById('rpNoticeModal');
            if (noticeEl) {
                const titleEl = noticeEl.querySelector('#rpNoticeModalLabel');
                const messageEl = noticeEl.querySelector('[data-rp-notice-message]');
                if (titleEl) titleEl.textContent = 'Success';
                if (messageEl) messageEl.textContent = successPopup;
                window.bootstrap.Modal.getOrCreateInstance(noticeEl).show();
            }
        }

        const openModal = @json(session('open_modal') ?: old('_modal'));
        const map = {
            settings: 'rpSettingsModal',
            profile: 'rpProfileModal',
            profile_password: 'rpProfileModal',
        };
        const targetId = map[openModal] || null;
        if (!targetId) return;
        const el = document.getElementById(targetId);
        if (el && window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
        }
    });
    </script>
</body>
</html>
