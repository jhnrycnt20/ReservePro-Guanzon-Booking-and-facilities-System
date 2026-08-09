@extends('layouts.dashboard')

@section('title', 'Guest Feedback')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Guest Feedback')
@section('page_subtitle', 'Ratings and comments from checked-out guests')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-card mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="fs-2" style="font-family: var(--rp-display);">{{ number_format($average ?? 0, 2) }}</div>
        <div class="text-muted">Average rating across all feedback</div>
    </div>
</div>
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Booking</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedback as $item)
                    <tr>
                        <td>{{ $item->guest?->user?->name ?? '—' }}</td>
                        <td>{{ $item->booking->booking_number ?? '—' }}</td>
                        <td>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $item->rating ? '-fill text-warning' : '' }}"></i>
                            @endfor
                        </td>
                        <td>{{ $item->comment ?: '—' }}</td>
                        <td>{{ $item->created_at?->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No feedback yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($feedback, 'links')) {{ $feedback->withQueryString()->links() }} @endif
</div>
@endsection
