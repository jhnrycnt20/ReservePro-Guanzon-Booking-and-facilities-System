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
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,600&family=Montserrat:wght@300;400;500;600&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}?v={{ file_exists(public_path('css/reservepro.css')) ? filemtime(public_path('css/reservepro.css')) : '1' }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="rp-public">
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
                    <img class="rp-nav-logo-img" src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
                </div>
                <div class="rp-nav-links rp-nav-links-main">
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ url('/') }}">The Resort</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('gallery') }}">Gallery</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('downloads') }}">Download</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('contact') }}">Contact</a>
                    <a class="rp-nav-link rp-nav-link-booknow" href="{{ route('accommodations.browse') }}">Book Now</a>
                    @guest
                        <a class="rp-nav-link" href="{{ route('login') }}" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Login</a>
                    @elseif(auth()->user()->hasRole('guest'))
                        <div class="dropdown rp-nav-account-dropdown">
                            <a class="rp-nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Account</a>
                            <ul class="dropdown-menu dropdown-menu-end rp-nav-account-menu">
                                <li><a class="dropdown-item" href="{{ route('guest.bookings.index') }}">My Reservations</a></li>
                                <li><a class="dropdown-item" href="{{ route('guest.payments.index') }}">Payments</a></li>
                                <li><a class="dropdown-item" href="{{ route('guest.feedback.create') }}">Write Feedback</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Manage Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavDesktopLogoutForm').submit();">Sign Out</a></li>
                            </ul>
                        </div>
                        <form id="rpNavDesktopLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @else
                        <a class="rp-nav-link" href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
                        <a class="rp-nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavDesktopLogoutForm').submit();">Sign Out</a>
                        <form id="rpNavDesktopLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <div class="rp-nav-overlay" id="rpNavOverlay">
        <img class="rp-nav-overlay-logo" src="{{ asset('images/guanzon_navbar_transparent.png') }}" alt="Guanzon Resort">
        <nav class="rp-nav-overlay-links">
            <a href="{{ url('/') }}">The Resort</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('downloads') }}">Download</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a href="{{ route('accommodations.browse') }}">Book Now</a>
            @auth
                @if(auth()->user()->hasRole('guest'))
                    <a href="{{ route('guest.bookings.index') }}">My Reservations</a>
                    <a href="{{ route('guest.payments.index') }}">Payments</a>
                    <a href="{{ route('guest.feedback.create') }}">Write Feedback</a>
                    <a href="{{ route('profile.edit') }}">Manage Profile</a>
                @else
                    <a href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
                @endif
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavOverlayLogoutForm').submit();">Sign Out</a>
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
    @guest
        @include('partials.login-modal')
        @include('partials.register-modal')
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    <script src="{{ asset('js/cookie-consent.js') }}?v={{ file_exists(public_path('js/cookie-consent.js')) ? filemtime(public_path('js/cookie-consent.js')) : '1' }}"></script>
    @stack('scripts')
</body>
</html>
