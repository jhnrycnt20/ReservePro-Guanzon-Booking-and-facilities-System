@extends('layouts.public')

@section('title', 'Download App')

@section('content')
@php
    $platforms = [
        'android' => [
            'label' => 'Android',
            'icon' => 'bi-android2',
            'steps' => [
                ['Open in Chrome', 'Visit this page in Chrome on your phone.'],
                ['Tap Install', 'Tap Install Guanzon App at the top of this page, or open the ⋮ menu in Chrome.'],
                ['Choose Install app', 'Tap Install app (or Add to Home screen), then confirm.'],
                ['Open ReservePro', 'Tap the new icon on your home screen to start booking.'],
            ],
        ],
        'ios' => [
            'label' => 'iPhone',
            'icon' => 'bi-apple',
            'steps' => [
                ['Open in Safari', 'Visit this page in Safari. Browsers inside other apps can\'t add to the home screen.'],
                ['Tap Share', 'Tap the Share button, the square with an arrow, at the bottom of the screen.'],
                ['Add to Home Screen', 'Scroll down the share sheet and tap Add to Home Screen.'],
                ['Tap Add', 'Confirm the name and tap Add. ReservePro appears on your home screen.'],
            ],
        ],
    ];
@endphp

<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>DOWNLOAD</span></h1>
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

<section class="rp-dl-install">
    <div class="container">
        <div class="rp-dl-install-grid">
            <div class="rp-dl-copy">
                <h2 class="rp-dl-headline">Guanzon Resort, one tap from your home screen</h2>
                <div class="rp-dl-cta">
                    <button type="button" id="pwaInstallBtn" class="rp-dl-btn">
                        <i class="bi bi-download" aria-hidden="true"></i>
                        Install Guanzon App
                    </button>
                    <a href="{{ route('accommodations.browse') }}" class="rp-dl-link">Or keep booking in your browser</a>
                </div>

                <div id="rpInstalledNote" class="rp-dl-installed d-none" role="status">
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    You're already using the ReservePro app.
                </div>
            </div>

            <div class="rp-dl-phone-wrap" aria-hidden="true">
                <div class="rp-dl-phone">
                    <div class="rp-dl-screen">
                        <div class="rp-dl-status">
                            <span>9:41</span>
                            <span class="rp-dl-island"></span>
                            <span class="rp-dl-status-icons"><i class="bi bi-wifi"></i><i class="bi bi-battery-full"></i></span>
                        </div>
                        <div class="rp-dl-home">
                            <div class="rp-dl-toast"><i class="bi bi-check-circle-fill"></i> Added to Home Screen</div>
                            <div class="rp-dl-grid">
                                @for ($i = 0; $i < 7; $i++)
                                    <span class="rp-dl-cell"><span class="rp-dl-icon"></span><span class="rp-dl-label"></span></span>
                                @endfor
                                <span class="rp-dl-cell rp-dl-app">
                                    <span class="rp-dl-app-tile"><img src="{{ asset('images/guanzon_logoW.png') }}" alt=""></span>
                                    <small>Guanzon</small>
                                </span>
                            </div>
                        </div>
                        <span class="rp-dl-bar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="rp-dl-steps" id="rpInstallSteps">
    <div class="container">
        <div class="rp-dl-steps-head">
            <h2 class="rp-dl-title">How to install</h2>
        </div>

        <div class="rp-dl-tabs">
            <div class="nav" role="tablist" aria-label="Choose your device">
                @foreach ($platforms as $key => $platform)
                    <button
                        type="button"
                        class="nav-link {{ $loop->first ? 'active' : '' }}"
                        id="rp-install-tab-{{ $key }}"
                        data-bs-toggle="tab"
                        data-bs-target="#rp-install-pane-{{ $key }}"
                        data-rp-install-tab="{{ $key }}"
                        role="tab"
                        aria-controls="rp-install-pane-{{ $key }}"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        <i class="bi {{ $platform['icon'] }}" aria-hidden="true"></i>
                        {{ $platform['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="tab-content">
            @foreach ($platforms as $key => $platform)
                <div
                    class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                    id="rp-install-pane-{{ $key }}"
                    role="tabpanel"
                    aria-labelledby="rp-install-tab-{{ $key }}"
                    tabindex="0"
                >
                    <ol class="rp-dl-steplist">
                        @foreach ($platform['steps'] as [$stepTitle, $stepText])
                            <li class="rp-dl-step">
                                <div class="rp-dl-step-top">
                                    <span class="rp-dl-step-num">{{ $loop->iteration }}</span>
                                </div>
                                <h3>{{ $stepTitle }}</h3>
                                <p>{{ $stepText }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endforeach
        </div>

        <p class="rp-dl-note">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            <span>Can't find the option? Use Safari on iPhone or Chrome on Android, and open this page directly rather than from inside Facebook or Messenger.</span>
        </p>
    </div>
</section>
@endsection
