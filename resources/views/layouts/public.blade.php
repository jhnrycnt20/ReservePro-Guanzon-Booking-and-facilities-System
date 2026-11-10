<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guanzon Resort') — Guanzon Resort</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}?v={{ file_exists(public_path('css/reservepro.css')) ? filemtime(public_path('css/reservepro.css')) : '1' }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="rp-public @yield('body_class')">
    <div class="rp-page-blur-wrap">
    <nav class="rp-public-nav">
        <div class="rp-public-nav-inner">
            <div class="rp-nav-row rp-nav-row-single">
                <div class="rp-nav-menu-btn">
                    <button type="button" class="rp-nav-hamburger-btn" id="rpNavMenuBtn" aria-label="Menu" aria-expanded="false" aria-controls="rpNavOverlay">
                        <span class="rp-nav-hamburger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </button>
                    <img class="rp-nav-logo-img" src="{{ asset('images/guanzon_navbar_transparent.png') }}?v={{ filemtime(public_path('images/guanzon_navbar_transparent.png')) }}" alt="Guanzon Resort">
                </div>
                <div class="rp-nav-links rp-nav-links-main">
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ url('/') }}">The Resort</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('gallery') }}">Gallery</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('offers') }}">Offers</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('contact') }}">Contact</a>
                    <a class="rp-nav-link rp-nav-link-booknow" href="{{ route('accommodations.browse') }}">Book Now</a>
                    @guest
                        <a class="rp-nav-link" href="{{ route('login') }}" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Login</a>
                    @else
                        <div class="dropdown rp-nav-account-dropdown">
                            <button class="rp-nav-link rp-nav-account-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Open account menu" title="Account" aria-expanded="false">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end rp-nav-account-menu">
                                <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#rpProfileModal"><i class="bi bi-person me-2" aria-hidden="true"></i>My account</button></li>
                                @if(auth()->user()?->isGuestRole())
                                    <li><a class="dropdown-item" href="{{ route('guest.bookings.index') }}"><i class="bi bi-calendar-check me-2" aria-hidden="true"></i>Reservations</a></li>
                                    <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#rpWriteFeedbackModal"><i class="bi bi-chat-square-text me-2" aria-hidden="true"></i>Write Feedback</button></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item rp-logout-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavDesktopLogoutForm').submit();"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Sign out</a></li>
                            </ul>
                        </div>
                        <form id="rpNavDesktopLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="rp-nav-overlay" id="rpNavOverlay">
        <img class="rp-nav-overlay-logo" src="{{ asset('images/guanzon_navbar_transparent.png') }}?v={{ filemtime(public_path('images/guanzon_navbar_transparent.png')) }}" alt="Guanzon Resort">
        <nav class="rp-nav-overlay-links">
            <a href="{{ url('/') }}">The Resort</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('offers') }}">Offers</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a href="{{ route('accommodations.browse') }}">Book Now</a>
            @auth
                @if(auth()->user()->hasRole('guest'))
                    <a href="{{ route('guest.bookings.index') }}">My Reservations</a>
                    <a href="{{ route('guest.payments.index') }}">Payments</a>
                    <a href="{{ route('guest.incidents.index') }}">Reports</a>
                    <a href="{{ route('guest.feedback.index') }}">My Feedback</a>
                    <a href="#rpProfileModal" data-bs-toggle="modal" data-bs-target="#rpProfileModal">Manage Profile</a>
                @else
                    <a href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
                @endif
                <a class="rp-logout-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavOverlayLogoutForm').submit();">Sign Out</a>
                <form id="rpNavOverlayLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @else
                <a href="{{ route('login') }}" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Log in</a>
            @endauth
        </nav>
        <div class="rp-nav-overlay-social">
            <div class="rp-nav-overlay-social-label">Connect With Us</div>
            <a href="https://www.facebook.com/profile.php?id=100057024897212" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        </div>
    </div>

    @yield('content')

    <footer class="rp-footer">
        <div class="container py-2 py-sm-3 py-md-5">
            <div class="row g-1 g-sm-2 g-md-4">
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Working Hours</div>
                    <div class="rp-footer-hours">
                        <p class="rp-footer-text">Front Desk: Open 24/7</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Location</div>
                    <p class="rp-footer-text">{{ $resortSettings['resort_address'] ?? 'Guanzon Beach Resort, Purok Lawis, Brgy. Langtad, City of Naga, Cebu' }}</p>
                    <a href="mailto:{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}" class="rp-footer-link-underline">{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}</a>
                    <p class="rp-footer-phone">{{ $resortSettings['resort_phone'] ?? '09190644054' }}</p>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Links</div>
                    <div class="rp-footer-links">
                        <a href="{{ url('/') }}">The Resort</a>
                        <a href="{{ route('gallery') }}">Gallery</a>
                        <a href="{{ route('downloads') }}">Download App</a>
                        <a href="{{ route('contact') }}">Contact us</a>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Get in Touch</div>
                    <div class="rp-footer-social mb-3">
                        <a href="https://www.facebook.com/profile.php?id=100057024897212" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </div>
            <div class="rp-footer-bottom">
                <div>ReservePro &copy; {{ date('Y') }}. All rights reserved.</div>
                <div class="rp-footer-legal-links">
                    <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#rpTermsModal">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
        <button type="button" class="rp-scroll-top" id="rpScrollTop" aria-label="Scroll to top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </footer>
    </div>

    <div class="rp-page-blur-overlay"></div>

    @include('partials.cookie-consent')
    @include('partials.terms-modal')
    @include('partials.confirm-modal')
    @include('partials.notice-modal')
    @auth
        @include('partials.modals.profile-modal')
        @if(auth()->user()?->isGuestRole())
            @include('partials.guest-feedback-modal')
        @endif
    @endauth
    @guest
        @include('partials.login-modal')
        @include('partials.register-modal')
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    <script src="{{ asset('js/cookie-consent.js') }}?v={{ file_exists(public_path('js/cookie-consent.js')) ? filemtime(public_path('js/cookie-consent.js')) : '1' }}"></script>
    @stack('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const successPopup = @json(session('success_popup'));
        if (successPopup && window.bootstrap) {
            const noticeEl = document.getElementById('rpNoticeModal');
            if (noticeEl) {
                const titleEl = noticeEl.querySelector('#rpNoticeModalLabel');
                const messageEl = noticeEl.querySelector('[data-rp-notice-message]');
                const iconEl = noticeEl.querySelector('[data-rp-notice-icon]');
                noticeEl.classList.add('is-success');
                if (iconEl) iconEl.innerHTML = '<i class="bi bi-check-lg"></i>';
                if (titleEl) titleEl.textContent = 'Success';
                if (messageEl) messageEl.textContent = successPopup;
                window.bootstrap.Modal.getOrCreateInstance(noticeEl).show();
            }
        }

        const openModal = @json(session('open_modal') ?: old('_modal'));
        const map = {
            profile: 'rpProfileModal',
            profile_password: 'rpProfileModal',
            feedback: 'rpWriteFeedbackModal',
        };
        const targetId = map[openModal] || null;
        if (!targetId || !window.bootstrap?.Modal) return;
        const el = document.getElementById(targetId);
        if (el) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
        }
    });
    </script>
</body>
</html>
