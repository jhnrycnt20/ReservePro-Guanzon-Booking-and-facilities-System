@extends('layouts.public')

@section('title', 'Privacy Policy')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>PRIVACY POLICY</span></h1>
            <div class="rp-hero-subtext">Guanzon Resort</div>
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
        <div class="rp-legal-content">
            <div class="rp-legal-updated">Last updated: {{ now()->format('F j, Y') }}</div>

            <div class="rp-legal-section" id="information-we-collect">
                <h2>The information we collect</h2>
                <p>When you browse our website, make a reservation, or contact us, we collect what you give us directly. That includes your name, email, phone number, booking dates, number of guests, special requests, and payment method. If you make a guest account, we also store your login details, and your password is kept encrypted. We never store full card or bank account numbers on our own servers.</p>
            </div>

            <div class="rp-legal-section" id="why-we-collect-it">
                <h2>Why we collect it</h2>
                <p>We use this information to process your reservation, verify payments, keep you updated about your stay, answer your questions, and keep your account safe. It also helps us improve our website and meet our legal and tax duties under the Philippine Data Privacy Act of 2012 (R.A. 10173).</p>
            </div>

            <div class="rp-legal-section" id="how-we-use-it">
                <h2>How we use and protect it</h2>
                <p>We only use your information for the reasons above. Passwords are encrypted, and only staff who need it can see your booking records. No website can promise perfect security, but we take reasonable steps to keep your data safe. We only keep booking and payment records for as long as we need them, and delete them securely once they're no longer required.</p>
            </div>

            <div class="rp-legal-section" id="who-we-share-it-with">
                <h2>Who we share it with</h2>
                <p>We never sell your personal information. We only share it with our own staff who help with your reservation, the payment channel you choose, service providers who support us (like our hosting provider), and government authorities if the law requires it.</p>
            </div>

            <div class="rp-legal-section" id="your-rights">
                <h2>Your rights</h2>
                <p>You can ask what personal information we have about you, ask us to fix anything that's wrong, or ask us to delete it, unless we're legally required to keep it. You can also opt out of optional messages at any time. Just reach out using the contact details below and we'll get back to you as soon as we can.</p>
            </div>

            <div class="rp-legal-section" id="cookies">
                <h2>Cookies</h2>
                <p>Cookies are small text files saved on your device that help our website remember your session and keep it secure. Right now we only use necessary cookies, to keep you logged in, protect forms from abuse, and remember your cookie choice. We don't use any analytics or advertising cookies. You can accept, reject, or manage optional cookies at any time.</p>
                <button type="button" class="rp-avail-btn-secondary rp-avail-btn-secondary--inline" data-rp-reopen-cookie-preferences>Manage Cookie Preferences</button>
            </div>

            <div class="rp-legal-section" id="contact">
                <h2>Contact us about privacy</h2>
                <p>If you have any questions about this Privacy Policy or how we handle your data, reach out to Guanzon Resort at Guanzon Beach, Bluepool Waterpark, Philippines. Email us at <a href="mailto:info@guanzonresort.com">info@guanzonresort.com</a> or call 09190644054.</p>
            </div>
        </div>
    </div>
</section>
@endsection
