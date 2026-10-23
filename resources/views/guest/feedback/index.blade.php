@extends('layouts.public')

@section('title', 'My Feedback')

@section('content')
<section class="py-5 rp-feedback-page">
    <div class="container rp-public-page-top">
        <div class="rp-page-intro mb-4">
            <h1 class="rp-page-intro-title mb-1">My Feedback</h1>
            <p class="text-muted mb-0">Share your review and see feedback you’ve already sent.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" data-rp-auto-dismiss>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="rp-feedback-top">
            <aside class="rp-feedback-avg-card">
                <h2 class="rp-feedback-card-title">Average Rating</h2>
                <div class="rp-feedback-avg-row">
                    <div class="rp-feedback-avg-score">{{ $count > 0 ? number_format($average, 1) : '0.0' }}</div>
                    <div class="rp-feedback-avg-stars" aria-label="Average {{ number_format($average, 1) }} out of 5">
                        @for($i = 1; $i <= 5; $i++)
                            @php
                                $fill = $count > 0 && $i <= floor($average);
                                $half = $count > 0 && ! $fill && $i === (int) ceil($average) && ($average - floor($average)) >= 0.25;
                            @endphp
                            <i class="bi bi-star{{ $fill ? '-fill' : ($half ? '-half' : '') }}" aria-hidden="true"></i>
                        @endfor
                    </div>
                </div>

                <div class="rp-feedback-bars" role="list" aria-label="Your rating breakdown">
                    @foreach(($ratingBreakdown ?? []) as $star => $row)
                        <div class="rp-feedback-bar-row" role="listitem">
                            <span class="rp-feedback-bar-label">{{ $star }}&nbsp;Star</span>
                            <div class="rp-feedback-bar-track" aria-hidden="true">
                                <span class="rp-feedback-bar-fill" style="width: {{ $row['percent'] }}%"></span>
                            </div>
                            <span class="rp-feedback-bar-pct">{{ $row['percent'] }}%</span>
                        </div>
                    @endforeach
                </div>
            </aside>

            <div class="rp-feedback-submit-card" id="rpWriteFeedback">
                <h2 class="rp-feedback-card-title">Submit Your Review</h2>

                <form method="POST" action="{{ route('guest.feedback.store') }}" class="rp-feedback-form" novalidate>
                    @csrf

                    <div class="rp-feedback-form-group">
                        <label class="rp-feedback-label">Add Your Rating</label>
                        @include('partials.star-rating-input', [
                            'ratingValue' => old('rating', 5),
                            'ratingId' => 'rpGuestFeedbackStars',
                        ])
                        @error('rating')<div class="rp-feedback-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="rp-feedback-form-group">
                        <label class="rp-feedback-label" for="rpGuestFeedbackName">Name <span class="rp-feedback-req">*</span></label>
                        <input
                            id="rpGuestFeedbackName"
                            type="text"
                            class="rp-feedback-input"
                            value="{{ $user->name }}"
                            readonly
                            aria-readonly="true"
                        >
                    </div>

                    <div class="rp-feedback-form-group">
                        <label class="rp-feedback-label" for="rpGuestFeedbackEmail">Email <span class="rp-feedback-req">*</span></label>
                        <input
                            id="rpGuestFeedbackEmail"
                            type="email"
                            class="rp-feedback-input"
                            value="{{ $user->email }}"
                            readonly
                            aria-readonly="true"
                        >
                    </div>

                    <div class="rp-feedback-form-group">
                        <label class="rp-feedback-label d-block">Feedback about</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="rpFeedbackResort" value="resort" @checked(old('scope', 'resort') === 'resort') required>
                                <label class="form-check-label" for="rpFeedbackResort">The resort</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="scope" id="rpFeedbackRoom" value="room" @checked(old('scope') === 'room')>
                                <label class="form-check-label" for="rpFeedbackRoom">A specific room</label>
                            </div>
                        </div>
                        @error('scope')<div class="rp-feedback-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="rp-feedback-form-group {{ old('scope') === 'room' ? '' : 'd-none' }}" id="rpFeedbackRoomWrap">
                        <label class="rp-feedback-label" for="rpFeedbackRoomSelect">Room</label>
                        <select name="accommodation_id" id="rpFeedbackRoomSelect" class="rp-feedback-input">
                            <option value="">Select a room</option>
                            @foreach($accommodations ?? [] as $room)
                                <option value="{{ $room->id }}" @selected((string) old('accommodation_id') === (string) $room->id)>{{ $room->name }}</option>
                            @endforeach
                        </select>
                        @error('accommodation_id')<div class="rp-feedback-field-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="rp-feedback-form-group">
                        <label class="rp-feedback-label" for="rpGuestFeedbackComment">Write Your Review <span class="rp-feedback-req">*</span></label>
                        <textarea
                            id="rpGuestFeedbackComment"
                            name="comment"
                            class="rp-feedback-textarea @error('comment') is-invalid @enderror"
                            rows="5"
                            placeholder="Write here..."
                            maxlength="2000"
                            required
                        >{{ old('comment') }}</textarea>
                        @error('comment')<div class="rp-feedback-field-error">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="rp-feedback-submit-btn">Submit Reviews</button>
                </form>

                @if(isset($bookings) && $bookings->isNotEmpty())
                    <div class="rp-feedback-stay-list mt-4">
                        <div class="rp-feedback-label mb-2">Or rate a checked-out stay</div>
                        <div class="list-group list-group-flush">
                            @foreach($bookings as $option)
                                <a href="{{ route('guest.feedback.create_booking', $option) }}" class="list-group-item list-group-item-action px-0">
                                    <div class="fw-semibold">{{ $option->booking_number }}</div>
                                    <div class="small text-muted">{{ $option->accommodation->name ?? 'Stay' }} · {{ $option->check_out_date?->format('M d, Y') }}</div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="rp-feedback-list-wrap">
            <h2 class="rp-feedback-list-title">Customer Feedbacks</h2>

            <div class="rp-feedback-list">
                @forelse($feedback as $item)
                    @php
                        $about = $item->booking
                            ? ($item->booking->accommodation->name ?? ('Stay '.$item->booking->booking_number))
                            : (($item->scope ?? '') === 'room'
                                ? ($item->accommodation->name ?? 'Room')
                                : 'Resort');
                    @endphp
                    <article class="rp-feedback-list-item">
                        <div class="rp-feedback-list-head">
                            <div>
                                <div class="rp-feedback-list-name">{{ $user->name }}</div>
                                <div class="rp-feedback-list-time">{{ $item->created_at?->diffForHumans() }} · {{ $about }}</div>
                            </div>
                            <div class="rp-feedback-list-stars" aria-label="{{ $item->rating }} out of 5 stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= $item->rating ? '-fill' : '' }}" aria-hidden="true"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="rp-feedback-list-comment">{{ $item->comment ?: 'No comment provided.' }}</p>
                    </article>
                @empty
                    <div class="rp-feedback-list-empty">
                        You have not submitted feedback yet. Use the form above to write your first review.
                    </div>
                @endforelse
            </div>

            @if(method_exists($feedback, 'links'))
                <div class="mt-4">{{ $feedback->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const roomWrap = document.getElementById('rpFeedbackRoomWrap');
    const radios = document.querySelectorAll('input[name="scope"]');
    if (roomWrap && radios.length) {
        const sync = () => {
            const selected = document.querySelector('input[name="scope"]:checked');
            roomWrap.classList.toggle('d-none', !selected || selected.value !== 'room');
        };
        radios.forEach((radio) => radio.addEventListener('change', sync));
        sync();
    }

    if (new URLSearchParams(window.location.search).get('write') === '1') {
        document.getElementById('rpWriteFeedback')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
@endpush
