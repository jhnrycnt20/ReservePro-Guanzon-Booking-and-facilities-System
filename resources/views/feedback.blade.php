@extends('layouts.public')

@section('title', 'Feedback')

@section('content')
<section class="rp-hero">
    <div class="rp-hero-full">
        <div class="rp-hero-inner">
            <h1><span>FEEDBACK</span></h1>
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

<section class="py-5 rp-public-feedback-section">
    <div class="container">
        <div class="rp-public-feedback-intro rp-public-feedback-intro--page">
            <div class="rp-cottages-kicker">GUEST STORIES</div>
            <h2 class="rp-public-feedback-title">What guests are saying</h2>
            <p class="rp-public-feedback-copy">Real feedback from previous stays at Guanzon Resort.</p>

            @if(($publicFeedbackCount ?? 0) > 0)
                <div class="rp-public-feedback-scoreline" aria-label="Average rating {{ number_format($publicFeedbackAverage, 1) }} from {{ $publicFeedbackCount }} reviews">
                    <span class="rp-public-feedback-avg">{{ number_format($publicFeedbackAverage, 1) }}</span>
                    <span class="rp-public-feedback-stars" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="bi bi-star{{ $i <= round($publicFeedbackAverage) ? '-fill' : '' }}"></i>
                        @endfor
                    </span>
                    <span class="rp-public-feedback-count">{{ $publicFeedbackCount }} review{{ $publicFeedbackCount === 1 ? '' : 's' }}</span>
                </div>
            @endif

            @auth
                @if(auth()->user()?->isGuestRole())
                    <button type="button" class="rp-public-feedback-cta" data-bs-toggle="modal" data-bs-target="#rpWriteFeedbackModal">Write feedback</button>
                @endif
            @else
                <button type="button" class="rp-public-feedback-cta rp-public-feedback-cta--ghost" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Sign in to write feedback</button>
            @endauth
        </div>

        <div class="rp-public-feedback-grid">
            @forelse($publicFeedback ?? [] as $item)
                @php
                    $fullName = $item->guest?->user?->name;
                    $displayName = $fullName ? explode(' ', trim($fullName))[0] : 'Guest';
                    $initial = strtoupper(mb_substr($displayName, 0, 1));
                    $about = $item->booking
                        ? ($item->booking->accommodation->name ?? 'Stay')
                        : (($item->scope ?? '') === 'room'
                            ? ($item->accommodation->name ?? 'Room')
                            : 'Resort');
                @endphp
                <article class="rp-public-feedback-card">
                    <div class="rp-public-feedback-card-top">
                        <div class="rp-public-feedback-stars" aria-label="{{ $item->rating }} out of 5 stars">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $item->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                            @endfor
                        </div>
                        <div class="rp-public-feedback-about">{{ $about }}</div>
                    </div>
                    <p class="rp-public-feedback-comment">“{{ \Illuminate\Support\Str::limit($item->comment, 220) }}”</p>
                    <div class="rp-public-feedback-card-foot">
                        <div class="rp-public-feedback-avatar" aria-hidden="true">{{ $initial }}</div>
                        <div class="rp-public-feedback-card-meta">
                            <span class="rp-public-feedback-author">{{ $displayName }}</span>
                            <span>{{ $item->created_at?->format('M j, Y') }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rp-public-feedback-empty">
                    No guest feedback yet. Sign in after your stay to share the first review.
                </div>
            @endforelse
        </div>

        @if(method_exists($publicFeedback ?? null, 'links'))
            <div class="mt-4 d-flex justify-content-center">{{ $publicFeedback->withQueryString()->links() }}</div>
        @endif
    </div>
</section>
@endsection
