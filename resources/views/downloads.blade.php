@extends('layouts.public')

@section('title', 'Download App')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>DOWNLOAD</span></h1>
            <div class="rp-hero-subtext">Guanzon Beach on your phone</div>
        </div>
    </div>
    <div class="rp-hero-scroll">
        <span class="rp-hero-scroll-line"></span>
        <span class="rp-hero-scroll-chevrons">
            <i class="bi bi-chevron-down"></i>
            <i class="bi bi-chevron-down"></i>
        </span>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="rp-page-intro text-center mb-4">
            <h2 class="rp-page-intro-title">Get the Guanzon Beach app</h2>
            <p class="rp-page-intro-text mx-auto" style="max-width: 36rem;">
                Browse rooms, book stays, pay with GCash, and manage your reservation from your phone.
            </p>
        </div>

        <div class="row g-4 align-items-stretch mb-5">
            <div class="col-md-4">
                <div class="rp-flow-card h-100 text-center">
                    <img src="{{ asset('images/rooms/cabana/01-exterior-row.png') }}" alt="Cabanas at Guanzon Beach" class="img-fluid rounded mb-3" style="height: 160px; width: 100%; object-fit: cover;">
                    <h3 class="h6">Stay by the shore</h3>
                    <p class="text-muted small mb-0">Cabanas, suites, rooms, and cottages for day trips or overnight rest.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-flow-card h-100 text-center">
                    <img src="{{ asset('images/landing-hero.png') }}" alt="Guanzon Beach waterpark" class="img-fluid rounded mb-3" style="height: 160px; width: 100%; object-fit: cover;">
                    <h3 class="h6">Bluepool Waterpark</h3>
                    <p class="text-muted small mb-0">Swim, unwind, and enjoy family-friendly amenities at Guanzon Beach.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-flow-card h-100 text-center">
                    <img src="{{ asset('images/offers/suite-promo.png') }}" alt="Suite sea view" class="img-fluid rounded mb-3" style="height: 160px; width: 100%; object-fit: cover;">
                    <h3 class="h6">Book and pay easily</h3>
                    <p class="text-muted small mb-0">Check dates, reserve online, and complete GCash payments in a few taps.</p>
                </div>
            </div>
        </div>

        <div class="rp-flow-card text-center mx-auto" style="max-width: 28rem;">
            <div class="rp-download-icon-wrap mb-3 mx-auto">
                <img
                    src="{{ asset('images/guanzon_logo_green.png') }}?v={{ filemtime(public_path('images/guanzon_logo_green.png')) }}"
                    alt="Guanzon Resort"
                    class="rp-download-app-icon"
                >
            </div>
            <h3 class="h5 mb-2">Install ReservePro</h3>
            <p class="text-muted small mb-4">Add the app to your home screen for faster booking next time.</p>
            <button type="button" id="pwaInstallBtn" class="rp-avail-btn-primary d-inline-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-download" aria-hidden="true"></i>
                Download / Install App
            </button>
            <div id="iosInstallHelp" class="rp-footer-install-help d-none mt-3 text-start">
                On iPhone: tap Share, then <strong>Add to Home Screen</strong>.
            </div>
            <div id="androidInstallHelp" class="rp-footer-install-help d-none mt-3 text-start">
                On Android: open the browser menu, then tap <strong>Install app</strong> or <strong>Add to Home screen</strong>.
            </div>
            <a href="{{ route('accommodations.browse') }}" class="rp-quiet-link d-inline-block mt-3">Or continue booking in the browser</a>
        </div>
    </div>
</section>
@endsection
