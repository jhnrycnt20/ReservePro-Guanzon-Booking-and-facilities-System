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
    <link href="{{ asset('css/reservepro.css') }}" rel="stylesheet">
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
                <a class="rp-nav-link rp-nav-link-extra" href="{{ route('blog') }}">Offers</a>
                <a class="rp-nav-link rp-nav-link-extra" href="{{ route('blog') }}">Contact</a>
                <a class="rp-nav-link rp-nav-link-booknow" href="{{ route('accommodations.browse') }}">Book Now</a>
            </div>
        </div>
    </nav>

    <div class="rp-nav-overlay" id="rpNavOverlay">
        <nav class="rp-nav-overlay-links">
            <a href="{{ url('/') }}">The Resort</a>
            <a href="{{ route('gallery') }}">Gallery</a>
            <a href="{{ route('blog') }}">Offers</a>
            <a href="{{ route('blog') }}">Contact</a>
            <a href="{{ route('accommodations.browse') }}">Book Now</a>
        </nav>
        <div class="rp-nav-overlay-social">
            <div class="rp-nav-overlay-social-label">Connect With Us</div>
            <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
        </div>
    </div>

    @include('partials.alerts')
    @yield('content')

    <div class="rp-insta-strip">
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1573843981267-be1999ff37cd?auto=format&fit=crop&w=500&q=80');"></a>
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?auto=format&fit=crop&w=500&q=80');"></a>
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=500&q=80');"></a>
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=500&q=80');"></a>
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1552733407-5d5c46c3bb3b?auto=format&fit=crop&w=500&q=80');"></a>
        <a href="#" class="rp-insta-cell" style="background-image: url('https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=500&q=80');"></a>
        <div class="rp-insta-badge"><i class="bi bi-instagram"></i></div>
    </div>

    <footer class="rp-footer">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-6 col-md-3">
                    <div class="rp-footer-heading">Working Hours</div>
                    <p class="rp-footer-text">Front Desk: Open 24/7</p>
                    <p class="rp-footer-text">Check-in: 2:00 PM</p>
                    <p class="rp-footer-text">Check-out: 12:00 PM</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rp-footer-heading">Office</div>
                    <p class="rp-footer-text">Guanzon Beach · Bluepool Waterpark</p>
                    <a href="mailto:info@guanzonresort.com" class="rp-footer-link-underline">info@guanzonresort.com</a>
                    <p class="rp-footer-phone">09190644054 · 265-7942</p>
                </div>
                <div class="col-6 col-md-3">
                    <div class="rp-footer-heading">Links</div>
                    <a href="{{ url('/') }}">The Resort</a>
                    <a href="{{ route('accommodations.browse') }}">Accommodations</a>
                    <a href="{{ route('gallery') }}">Gallery</a>
                    <a href="{{ route('blog') }}">Offers</a>
                    <a href="{{ route('blog') }}">Contact us</a>
                </div>
                <div class="col-6 col-md-3">
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
    <script src="{{ asset('js/reservepro.js') }}"></script>
    @stack('scripts')
</body>
</html>
