@extends('layouts.dashboard')

@section('title', 'Guest Feedback')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Guest Feedback')
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
@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'rating',
            'label' => 'Rating',
            'empty' => 'All ratings',
            'value' => request('rating'),
            'options' => [
                '5' => '5 stars',
                '4' => '4 stars',
                '3' => '3 stars',
                '2' => '2 stars',
                '1' => '1 star',
            ],
        ],
    ],
    'searchPlaceholder' => 'Guest, Booking Number, or Comment',
    'clearUrl' => route('admin.feedback.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 14%;">Guest</th>
                    <th style="width: 16%;">About</th>
                    <th style="width: 12%;">Rating</th>
                    <th style="width: 44%;">Comment</th>
                    <th style="width: 14%;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedback as $item)
                    <tr>
                        <td>{{ $item->guest?->user?->name ?? '—' }}</td>
                        <td>
                            @if($item->booking)
                                {{ $item->booking->booking_number }}
                            @elseif(($item->scope ?? 'stay') === 'room')
                                Room: {{ $item->accommodation->name ?? '—' }}
                            @else
                                Resort
                            @endif
                        </td>
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
