<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    @php $rpNavMinimal = request()->routeIs('guest.bookings.index', 'guest.bookings.show', 'guest.payments.index', 'guest.incidents.index', 'guest.incidents.show', 'guest.incidents.create'); @endphp
    <nav class="rp-public-nav @if($rpNavMinimal) rp-public-nav--minimal @endif">
        <div class="rp-public-nav-inner">
            <div class="rp-nav-menu-btn">
                <button type="button" class="rp-nav-hamburger-btn" id="rpNavMenuBtn" aria-label="Menu" aria-expanded="false" aria-controls="rpNavOverlay">
                    <span class="rp-nav-hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                @unless($rpNavMinimal)
                    <a href="{{ route('accommodations.browse') }}">
                        <img class="rp-nav-logo-img" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
                    </a>
                @endunless
            </div>
            @unless($rpNavMinimal)
                <div class="rp-nav-links">
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ url('/') }}">The Resort</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('gallery') }}">Gallery</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('offers') }}">Offers</a>
                    <a class="rp-nav-link rp-nav-link-extra" href="{{ route('contact') }}">Contact</a>
                    <div class="rp-nav-actions">
                        @unless (request()->routeIs('accommodations.*') || request()->routeIs('guest.bookings.create'))
                            <a class="rp-nav-link rp-nav-link-booknow" href="{{ route('accommodations.browse') }}">Book Now</a>
                        @endunless
                    </div>
                </div>
            @endunless
        </div>
    </nav>

    <div class="rp-nav-overlay" id="rpNavOverlay">
        <nav class="rp-nav-overlay-links">
            <a href="{{ url('/') }}">The Resort</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('offers') }}">Offers</a>
            <a href="{{ route('contact') }}">Contact</a>
            @auth
                @if(auth()->user()->hasRole('guest'))
                    <a href="{{ route('guest.bookings.index') }}">My Reservations</a>
                    <a href="{{ route('guest.payments.index') }}">Payments</a>
                @endif
                <a href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('rpNavOverlayLogoutForm').submit();">Sign Out</a>
                <form id="rpNavOverlayLogoutForm" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @else
                <a href="{{ route('login') }}">Log in</a>
            @endauth
        </nav>
        <div class="rp-nav-overlay-social">
            <div class="rp-nav-overlay-social-label">Connect With Us</div>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        </div>
    </div>

    @include('partials.alerts')
    @yield('content')

    <footer class="rp-footer">
        <div class="container py-2 py-sm-3 py-md-5">
            <div class="row g-1 g-sm-2 g-md-4">
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Working Hours</div>
                    <div class="rp-footer-hours">
                        <p class="rp-footer-text">Front Desk: Open 24/7</p>
                        <p class="rp-footer-text">Check-in: {{ $resortSettings['check_in_time'] ?? '14:00' }}</p>
                        <p class="rp-footer-text">Check-out: {{ $resortSettings['check_out_time'] ?? '12:00' }}</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Location</div>
                    <p class="rp-footer-text mb-0">{{ $resortSettings['resort_name'] ?? 'Guanzon Beach' }}</p>
                    <p class="rp-footer-text mb-0">{{ $resortSettings['resort_subtitle'] ?? 'Bluepool Waterpark' }}</p>
                    <p class="rp-footer-text">{{ $resortSettings['resort_address'] ?? 'Philippines' }}</p>
                    <a href="mailto:{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}" class="rp-footer-link-underline">{{ $resortSettings['resort_email'] ?? 'info@guanzonresort.com' }}</a>
                    <p class="rp-footer-phone">{{ $resortSettings['resort_phone'] ?? '09190644054' }}@if(!empty($resortSettings['resort_phone_landline'])) · {{ $resortSettings['resort_phone_landline'] }}@endif</p>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Links</div>
                    <div class="rp-footer-links">
                        <a href="{{ url('/') }}">The Resort</a>
                        <a href="{{ route('gallery') }}">Gallery</a>
                        <a href="{{ route('offers') }}">Offers</a>
                        <a href="{{ route('contact') }}">Contact us</a>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Get in Touch</div>
                    <div class="rp-footer-social mb-3">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    </div>
                    <button type="button" id="pwaInstallBtn" class="rp-footer-install-btn">
                        <i class="bi bi-download me-1"></i> Install App
                    </button>
                    <div id="iosInstallHelp" class="rp-footer-install-help d-none mt-2">
                        <div class="small">
                            <strong>Install on iPhone:</strong>
                            tap Share, then Add to Home Screen.
                        </div>
                    </div>
                    <div id="androidInstallHelp" class="rp-footer-install-help d-none mt-2">
                        <div class="small">
                            <strong>Install tip:</strong>
                            open the browser menu and choose <em>Install app</em> / <em>Add to Home screen</em>.
                        </div>
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

    @include('partials.cookie-consent')
    @include('partials.terms-modal')
    @include('partials.confirm-modal')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    <script src="{{ asset('js/cookie-consent.js') }}?v={{ file_exists(public_path('js/cookie-consent.js')) ? filemtime(public_path('js/cookie-consent.js')) : '1' }}"></script>
    @stack('scripts')
</body>
</html>
