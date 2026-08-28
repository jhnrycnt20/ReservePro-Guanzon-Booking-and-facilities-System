@extends('layouts.public')

@section('title', 'Resort Stay Made Simple')

@section('content')
<section class="rp-hero">
    <div class="container py-5">
        <div class="rp-hero-inner">
            <div class="rp-brand-name mb-2" style="font-family: var(--rp-display); font-size: 1.1rem; letter-spacing: .08em; text-transform: uppercase; opacity: .9;">ReservePro</div>
            <h1>Your Guanzon resort stay, managed end to end.</h1>
            <p>Browse rooms and cottages, check availability, reserve with confidence, and track every step from approval to check-out.</p>
            <div class="d-flex flex-wrap gap-2 mt-4">
                <a href="{{ route('accommodations.browse') }}" class="btn btn-light btn-lg">Browse Resort</a>
                <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Create Guest Account</a>
                <button type="button" id="pwaInstallBtn" class="btn btn-outline-light btn-lg d-none">
                    <i class="bi bi-download me-1"></i> Install App
                </button>
            </div>
            <div id="iosInstallHelp" class="rp-ios-install d-none mt-3">
                <div class="small">
                    <strong>Install on iPhone:</strong>
                    tap <i class="bi bi-box-arrow-up"></i> <em>Share</em>, then
                    <strong>Add to Home Screen</strong>.
                    Use Safari for the best result.
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="rp-card h-100">
                    <i class="bi bi-calendar2-check text-success fs-3"></i>
                    <h3 class="h5 mt-3">Reserve with clarity</h3>
                    <p class="text-muted mb-0">Check real availability, submit a reservation, and follow pending, approved, or rejected status in one place.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-card h-100">
                    <i class="bi bi-shield-check text-success fs-3"></i>
                    <h3 class="h5 mt-3">Stay support built in</h3>
                    <p class="text-muted mb-0">Report broken amenities or incidents, track investigation progress, and get notified on every update.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="rp-card h-100">
                    <i class="bi bi-receipt text-success fs-3"></i>
                    <h3 class="h5 mt-3">Payments & receipts</h3>
                    <p class="text-muted mb-0">Pay after approval, keep balances accurate, and receive verified receipts from front desk staff.</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
