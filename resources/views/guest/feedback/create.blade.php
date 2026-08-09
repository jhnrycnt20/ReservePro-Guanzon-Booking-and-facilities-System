@extends('layouts.dashboard')

@section('title', 'Leave Feedback')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Leave Feedback')
@section('page_subtitle', 'Share your stay experience')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="rp-card">
            @if(isset($booking) && $booking)
                <form method="POST" action="{{ route('guest.feedback.store', $booking) }}">
                    @csrf
                    <div class="mb-3">
                        <div class="text-muted small">Booking</div>
                        <div class="fw-semibold">{{ $booking->booking_number }} — {{ $booking->accommodation->name ?? 'Stay' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <select name="rating" class="form-select" required>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} — {{ ['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'][$i] }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comment</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="What did you enjoy? What can we improve?" maxlength="2000">{{ old('comment') }}</textarea>
                    </div>
                    <button class="btn btn-rp-primary w-100">Submit Feedback</button>
                </form>
            @elseif(isset($bookings) && $bookings->isNotEmpty())
                <p class="text-muted mb-3">Choose a checked-out stay to rate:</p>
                <div class="list-group list-group-flush">
                    @foreach($bookings as $option)
                        <a href="{{ route('guest.feedback.create', $option) }}" class="list-group-item list-group-item-action px-0">
                            <div class="fw-semibold">{{ $option->booking_number }}</div>
                            <div class="small text-muted">{{ $option->accommodation->name ?? 'Stay' }} · {{ $option->check_out_date?->format('M d, Y') }}</div>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No checked-out bookings are available for feedback.</p>
                <a href="{{ route('guest.feedback.index') }}" class="btn btn-rp-soft mt-3">Back to feedback</a>
            @endif
        </div>
    </div>
</div>
@endsection
