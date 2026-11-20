@extends('layouts.dashboard')

@section('title', 'Guest Feedback')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Guest Feedback')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="rp-feedback-toolbar">
    <div class="rp-feedback-average" aria-label="Average rating {{ number_format($average ?? 0, 2) }} out of 5">
        <span>Average rating</span>
        <strong class="rp-feedback-summary-value">{{ number_format($average ?? 0, 2) }}</strong>
        <span class="rp-feedback-average-scale">/ 5</span>
    </div>
    <div class="rp-feedback-controls rp-admin-list-toolbar">
        <form method="GET" class="rp-admin-list-search" data-rp-live-filter>
            <div class="rp-resv-search">
                <label for="rpFeedbackSearch" class="visually-hidden">Search feedback</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                    id="rpFeedbackSearch"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    class="form-control"
                    placeholder="Search by guest, booking number, or comment..."
                    data-rp-live-filter-q
                    autocomplete="off"
                >
            </div>
        </form>
    </div>
</div>
<div class="rp-card rp-admin-list-card rp-admin-table-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--feedback mb-0">
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
    @if(method_exists($feedback, 'links') && $feedback->total() > 0)
        <div class="rp-resv-footer">
            <div class="rp-resv-footer-meta">
                Showing {{ $feedback->firstItem() }}-{{ $feedback->lastItem() }} of {{ $feedback->total() }} feedback entries
            </div>
            <div class="rp-resv-pagination">
                @if($feedback->onFirstPage())
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $feedback->withQueryString()->previousPageUrl() }}" class="rp-resv-page" aria-label="Previous page"><i class="bi bi-chevron-left"></i></a>
                @endif
                @for($page = 1; $page <= $feedback->lastPage(); $page++)
                    @if($page === $feedback->currentPage())
                        <span class="rp-resv-page is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $feedback->withQueryString()->url($page) }}" class="rp-resv-page" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endfor
                @if($feedback->hasMorePages())
                    <a href="{{ $feedback->withQueryString()->nextPageUrl() }}" class="rp-resv-page" aria-label="Next page"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="rp-resv-page is-disabled" aria-disabled="true"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
