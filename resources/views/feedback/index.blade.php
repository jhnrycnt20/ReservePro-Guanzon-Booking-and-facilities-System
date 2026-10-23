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
        <div class="rp-public-feedback-head">
            <div class="rp-public-feedback-intro">
                <h2 class="rp-public-feedback-title">What guests are saying</h2>
                <p class="rp-public-feedback-copy mb-0">Read reviews from visitors. Sign in to share your own experience.</p>
            </div>
            <div class="rp-public-feedback-summary">
                @if($count > 0)
                    <div class="rp-public-feedback-score">
                        <div class="rp-public-feedback-avg">{{ number_format($average, 1) }}</div>
                        <div class="rp-public-feedback-avg-meta">
                            <div class="rp-public-feedback-stars" aria-label="Average {{ number_format($average, 1) }} out of 5">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= round($average) ? '-fill' : '' }}" aria-hidden="true"></i>
                                @endfor
                            </div>
                            <div>{{ $count }} review{{ $count === 1 ? '' : 's' }}</div>
                        </div>
                    </div>
                @endif
                @auth
                    @if(auth()->user()->hasRole('guest'))
                        <a href="{{ route('guest.feedback.index') }}" class="btn btn-rp-primary">Write Feedback</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-rp-primary" data-bs-toggle="modal" data-bs-target="#rpLoginModal">Sign in to write feedback</a>
                @endauth
            </div>
        </div>

        <div class="rp-public-feedback-grid">
            @forelse($feedback as $item)
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
                    <p class="rp-public-feedback-comment">{{ $item->comment ?: 'No written comment.' }}</p>
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
                    No feedback yet. Be the first to share your stay once you sign in.
                </div>
            @endforelse
        </div>

        @if(method_exists($feedback, 'links'))
            <div class="mt-4">{{ $feedback->withQueryString()->links() }}</div>
        @endif
    </div>
</section>
@endsection
