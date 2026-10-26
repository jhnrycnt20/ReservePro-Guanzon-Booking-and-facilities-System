@extends('layouts.dashboard')

@section('title', 'Guest Feedback')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Guest Feedback')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-card rp-feedback-summary mb-3">
    <div class="d-flex align-items-center gap-3">
        <div class="rp-feedback-summary-value">{{ number_format($average ?? 0, 2) }}</div>
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
<div class="rp-card rp-admin-list-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--feedback">
            <thead>
                <tr>
                    <th scope="col" style="width: 14%;">Guest</th>
                    <th scope="col" style="width: 16%;">About</th>
                    <th scope="col" style="width: 12%;">Rating</th>
                    <th scope="col" style="width: 44%;">Comment</th>
                    <th scope="col" style="width: 14%;">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($feedback as $item)
                    <tr>
                        <td class="rp-admin-list-primary">{{ $item->guest?->user?->name ?? 'Visitor' }}</td>
                        <td>
                            @if($item->booking)
                                {{ $item->booking->booking_number }}
                            @elseif(($item->scope ?? 'stay') === 'room')
                                Room: {{ $item->accommodation->name ?? '—' }}
                            @else
                                Resort
                            @endif
                        </td>
                        <td class="rp-feedback-rating">
                            <span class="visually-hidden">{{ $item->rating }} out of 5 stars</span>
                            @for($i = 1; $i <= 5; $i++)
                                <i aria-hidden="true" class="bi bi-star{{ $i <= $item->rating ? '-fill text-warning' : '' }}"></i>
                            @endfor
                        </td>
                        <td class="rp-admin-list-wrap">{{ $item->comment ?: '—' }}</td>
                        <td class="rp-admin-list-mono">{{ $item->created_at?->format('M d, Y') }}</td>
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
