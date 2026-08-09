@extends('layouts.dashboard')

@section('title', 'Feedback')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'My Feedback')
@section('page_subtitle', 'Ratings you have shared after check-out')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedback as $item)
                    <tr>
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
                    <tr><td colspan="4" class="text-muted">You have not submitted feedback yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($feedback, 'links')) {{ $feedback->withQueryString()->links() }} @endif
</div>
@endsection
