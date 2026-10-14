@extends('layouts.public')

@section('title', 'Write Feedback')

@section('content')
<div class="container rp-public-page-top pb-4">
    @auth
        @if(auth()->user()->hasRole('guest'))
            <a href="{{ route('guest.feedback.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Feedback</a>
        @else
            <a href="{{ url('/') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>
        @endif
    @else
        <a href="{{ url('/') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to Home</a>
    @endauth

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Write Feedback</h1>
        <p class="text-muted mb-0">Share your thoughts about a room or the resort. No account needed.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" data-rp-auto-dismiss>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="rp-flow-card">
                @if(isset($booking) && $booking)
                    <form method="POST" action="{{ route('guest.feedback.store_booking', $booking) }}">
                        @csrf
                        <div class="mb-3">
                            <div class="text-muted small">Stay feedback</div>
                            <div class="fw-semibold">{{ $booking->booking_number }} — {{ $booking->accommodation->name ?? 'Stay' }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            @include('partials.star-rating-input', ['ratingValue' => old('rating', 5), 'ratingId' => 'rpFeedbackBookingStars'])
                            @error('rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="What did you enjoy? What can we improve?" maxlength="2000">{{ old('comment') }}</textarea>
                            @error('comment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-rp-primary w-100">Submit Feedback</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('feedback.store') }}" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label d-block">Feedback about</label>
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
                            @error('scope')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3 {{ old('scope') === 'room' ? '' : 'd-none' }}" id="rpFeedbackRoomWrap">
                            <label class="form-label" for="rpFeedbackRoomSelect">Room</label>
                            <select name="accommodation_id" id="rpFeedbackRoomSelect" class="form-select">
                                <option value="">Select a room</option>
                                @foreach($accommodations ?? [] as $room)
                                    <option value="{{ $room->id }}" @selected((string) old('accommodation_id') === (string) $room->id)>{{ $room->name }}</option>
                                @endforeach
                            </select>
                            @error('accommodation_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            @include('partials.star-rating-input', ['ratingValue' => old('rating', 5), 'ratingId' => 'rpFeedbackStars'])
                            @error('rating')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Tell us about your experience" maxlength="2000">{{ old('comment') }}</textarea>
                            @error('comment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-rp-primary w-100">Submit Feedback</button>
                    </form>

                    @if(isset($bookings) && $bookings->isNotEmpty())
                        <hr>
                        <p class="text-muted mb-3">Or rate a checked-out stay:</p>
                        <div class="list-group list-group-flush">
                            @foreach($bookings as $option)
                                <a href="{{ route('guest.feedback.create_booking', $option) }}" class="list-group-item list-group-item-action px-0">
                                    <div class="fw-semibold">{{ $option->booking_number }}</div>
                                    <div class="small text-muted">{{ $option->accommodation->name ?? 'Stay' }} · {{ $option->check_out_date?->format('M d, Y') }}</div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
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
});
</script>
@endpush
