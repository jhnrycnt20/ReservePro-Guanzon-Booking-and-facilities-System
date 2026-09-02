<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Guanzon Resort - Coastal Retreat</title>
    @include('partials.pwa-head')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,600&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="{{ asset('css/reservepro.css') }}?v={{ file_exists(public_path('css/reservepro.css')) ? filemtime(public_path('css/reservepro.css')) : '1' }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="rp-public">
    <nav class="rp-public-nav">
        <div class="rp-public-nav-inner">
            <div class="rp-nav-menu-btn">
                <button type="button" class="rp-nav-hamburger-btn" id="rpNavMenuBtn" aria-label="Menu" aria-expanded="false" aria-controls="rpNavOverlay">
                    <span class="rp-nav-hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <a href="{{ route('accommodations.browse') }}">
                    <img class="rp-nav-logo-img" src="{{ asset('images/guanzon_logoW.png') }}" alt="Guanzon Resort">
                </a>
            </div>
            <div class="rp-nav-links">
                <a class="rp-nav-link rp-nav-link-extra" href="{{ url('/') }}">The Resort</a>
                <a class="rp-nav-link rp-nav-link-extra" href="{{ route('gallery') }}">Gallery</a>
                <a class="rp-nav-link rp-nav-link-extra" href="{{ route('offers') }}">Offers</a>
                <a class="rp-nav-link rp-nav-link-extra" href="{{ route('contact') }}">Contact</a>
                <div class="rp-nav-actions">
                    @auth
                        <a class="rp-nav-auth-btn" href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
                    @endauth
                    @unless (request()->routeIs('accommodations.*'))
                        <a class="rp-nav-link rp-nav-link-booknow" href="{{ route('accommodations.browse') }}">Book Now</a>
                    @endunless
                </div>
            </div>
        </div>
    </nav>

    <div class="rp-nav-overlay" id="rpNavOverlay">
        <nav class="rp-nav-overlay-links">
            <a href="{{ url('/') }}">The Resort</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('offers') }}">Offers</a>
            <a href="{{ route('contact') }}">Contact</a>
            @auth
                <a href="{{ \App\Helpers\RoleRedirect::dashboardRoute() }}">Account</a>
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
                        <p class="rp-footer-text">Check-in: 2:00 PM</p>
                        <p class="rp-footer-text">Check-out: 12:00 PM</p>
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="rp-footer-heading">Location</div>
                    <p class="rp-footer-text">Guanzon Beach · Bluepool Waterpark</p>
                    <a href="mailto:info@guanzonresort.com" class="rp-footer-link-underline">info@guanzonresort.com</a>
                    <p class="rp-footer-phone">09190644054 · 265-7942</p>
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
                    <div class="rp-footer-social">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    </div>
                </div>
            </div>
            <div class="rp-footer-bottom">
                <div>ReservePro &copy; {{ date('Y') }}. All rights reserved.</div>
            </div>
        </div>
        <button type="button" class="rp-scroll-top" id="rpScrollTop" aria-label="Scroll to top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/reservepro.js') }}?v={{ file_exists(public_path('js/reservepro.js')) ? filemtime(public_path('js/reservepro.js')) : '1' }}"></script>
    @stack('scripts')
</body>
</html>
