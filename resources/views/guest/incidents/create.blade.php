@extends('layouts.public')

@section('title', 'Submit Report')

@section('content')
<div class="container rp-public-page-top pb-4">
    <a href="{{ route('guest.bookings.index') }}" class="rp-back-link"><i class="bi bi-arrow-left"></i> Back to My Reservations</a>

    <div class="rp-page-intro">
        <h1 class="rp-page-intro-title">Report Incident</h1>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="rp-flow-card">
                <form method="POST" action="{{ route('guest.incidents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Related booking</label>
                            @php $rpLockedBooking = $bookings->firstWhere('id', (int) old('booking_id', request('booking_id'))); @endphp
                            @if($rpLockedBooking)
                                <select class="form-select" disabled>
                                    <option selected>{{ $rpLockedBooking->booking_number }} — {{ $rpLockedBooking->accommodation->name }}</option>
                                </select>
                                <input type="hidden" name="booking_id" value="{{ $rpLockedBooking->id }}">
                            @else
                                <select name="booking_id" class="form-select" required>
                                    @foreach($bookings as $booking)
                                        <option value="{{ $booking->id }}" @selected(old('booking_id') == $booking->id)>
                                            {{ $booking->booking_number }} — {{ $booking->accommodation->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Report type</label>
                            <select name="report_type" class="form-select" required>
                                @foreach(['incident' => 'Incident', 'broken_amenity' => 'Broken Amenity', 'complaint' => 'Complaint', 'maintenance' => 'Maintenance'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('report_type') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" name="location" class="form-control" value="{{ old('location') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, or WEBP. Max 5MB.</div>
                        </div>
                    </div>
                    <button class="rp-btn-check-availability mt-3">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
