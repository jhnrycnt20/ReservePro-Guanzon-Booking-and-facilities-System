@extends('layouts.dashboard')

@section('title', 'Reservations')
@section('theme', 'front_desk')
@section('role_label', 'Front Desk')
@section('page_title', 'Reservations')
@section('sidebar')
    @include('partials.sidebar-front-desk')
@endsection

@section('content')
@php
    $dateFromDisplay = $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('M j, Y') : null;
    $dateToDisplay = $dateTo ? \Carbon\Carbon::parse($dateTo)->format('M j, Y') : null;
    $dateLabel = ($dateFromDisplay && $dateToDisplay)
        ? $dateFromDisplay.' - '.$dateToDisplay
        : ($dateFromDisplay ?: ($dateToDisplay ?: 'Date range'));
@endphp

{{-- Success/errors for open modals render inside those modals so they stay visible. --}}
@if(session('success') && ! session('open_view') && ! session('open_checkout') && old('_modal') !== 'walkin')
    <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
@endif
@if($errors->any() && old('_modal') !== 'walkin')
    <div class="alert alert-danger border-0 shadow-sm mb-3">
        {{ $errors->first() }}
    </div>
@endif

<div class="rp-resv-toolbar mb-3">
    <div class="rp-resv-tabs" role="tablist" aria-label="Reservation filters">
        <a href="{{ route('front_desk.reservations.index', array_filter(['q' => request('q'), 'from' => $dateFrom, 'to' => $dateTo, 'tab' => 'all'])) }}"
           class="rp-resv-tab {{ $tab === 'all' ? 'is-active' : '' }}">All</a>
        <a href="{{ route('front_desk.reservations.index', array_filter(['q' => request('q'), 'from' => $dateFrom, 'to' => $dateTo, 'tab' => 'check_in'])) }}"
           class="rp-resv-tab {{ $tab === 'check_in' ? 'is-active' : '' }}">
            <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Check-in
        </a>
        <a href="{{ route('front_desk.reservations.index', array_filter(['q' => request('q'), 'from' => $dateFrom, 'to' => $dateTo, 'tab' => 'check_out'])) }}"
           class="rp-resv-tab {{ $tab === 'check_out' ? 'is-active' : '' }}">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Check-out
        </a>
        <a href="{{ route('front_desk.reservations.index', array_filter(['q' => request('q'), 'from' => $dateFrom, 'to' => $dateTo, 'tab' => 'walk_in'])) }}"
           class="rp-resv-tab {{ $tab === 'walk_in' ? 'is-active' : '' }}">
            <i class="bi bi-person-walking" aria-hidden="true"></i> Walk-in
        </a>
        <a href="{{ route('front_desk.reservations.index', array_filter(['q' => request('q'), 'from' => $dateFrom, 'to' => $dateTo, 'tab' => 'logs'])) }}"
           class="rp-resv-tab {{ $tab === 'logs' ? 'is-active' : '' }}">
            <i class="bi bi-clock-history" aria-hidden="true"></i> Logs
        </a>
    </div>

    <form method="GET" class="rp-resv-search-form" data-rp-live-filter>
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="rp-resv-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                class="form-control"
                placeholder="Search by guest name, reservation ID..."
                data-rp-live-filter-q
                autocomplete="off"
            >
        </div>
        <div class="rp-resv-tools">
            <div class="rp-resv-date" data-rp-resv-date>
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <button type="button" class="rp-resv-date-btn" data-rp-resv-date-toggle>
                    <span>{{ $dateLabel }}</span>
                    <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="rp-resv-date-panel" data-rp-resv-date-panel hidden>
                    <label class="form-label">From</label>
                    <input type="date" name="from" value="{{ $dateFrom }}" class="form-control mb-2" data-rp-live-filter-change>
                    <label class="form-label">To</label>
                    <input type="date" name="to" value="{{ $dateTo }}" class="form-control mb-2" data-rp-live-filter-change>
                    <a href="{{ route('front_desk.reservations.index', array_filter(['tab' => $tab, 'q' => request('q')])) }}" class="btn btn-rp-soft btn-sm w-100">Clear dates</a>
                </div>
            </div>
            <button type="button" class="btn btn-rp-primary rp-resv-walkin-btn" data-bs-toggle="modal" data-bs-target="#rpWalkInModal">
                <i class="bi bi-person-walking me-1" aria-hidden="true"></i> New Walk-in
            </button>
        </div>
    </form>
</div>

<div class="rp-resv-stats mb-4">
    <a href="{{ route('front_desk.reservations.index', ['tab' => 'all']) }}" class="rp-resv-stat is-total">
        <div class="rp-resv-stat-icon"><i class="bi bi-calendar3"></i></div>
        <div class="rp-resv-stat-body">
            <div class="rp-resv-stat-label">Total Reservations</div>
            <div class="rp-resv-stat-value">{{ number_format($stats['total']) }}</div>
            <div class="rp-resv-stat-meta">Open stays in queue</div>
        </div>
        <i class="bi bi-chevron-right rp-resv-stat-arrow" aria-hidden="true"></i>
    </a>
    <a href="{{ route('front_desk.reservations.index', ['tab' => 'check_in']) }}" class="rp-resv-stat is-checkin">
        <div class="rp-resv-stat-icon"><i class="bi bi-box-arrow-in-right"></i></div>
        <div class="rp-resv-stat-body">
            <div class="rp-resv-stat-label">Check-ins Today</div>
            <div class="rp-resv-stat-value">{{ number_format($stats['check_ins_today']) }}</div>
            <div class="rp-resv-stat-meta">{{ $stats['check_ins_ready'] }} ready · {{ $stats['check_ins_today_pending'] }} pending balance</div>
        </div>
        <i class="bi bi-chevron-right rp-resv-stat-arrow" aria-hidden="true"></i>
    </a>
    <a href="{{ route('front_desk.reservations.index', ['tab' => 'check_out']) }}" class="rp-resv-stat is-checkout">
        <div class="rp-resv-stat-icon"><i class="bi bi-box-arrow-right"></i></div>
        <div class="rp-resv-stat-body">
            <div class="rp-resv-stat-label">Due for check-out</div>
            <div class="rp-resv-stat-value">{{ number_format($stats['check_outs_today_due']) }}</div>
            <div class="rp-resv-stat-meta">{{ $stats['check_outs_today'] }} scheduled today</div>
        </div>
        <i class="bi bi-chevron-right rp-resv-stat-arrow" aria-hidden="true"></i>
    </a>
    <a href="{{ route('front_desk.reservations.index', ['tab' => 'walk_in']) }}" class="rp-resv-stat is-walkin">
        <div class="rp-resv-stat-icon"><i class="bi bi-person"></i></div>
        <div class="rp-resv-stat-body">
            <div class="rp-resv-stat-label">Walk-ins Today</div>
            <div class="rp-resv-stat-value">{{ number_format($stats['walk_ins_today']) }}</div>
            <div class="rp-resv-stat-meta">Fully paid · today</div>
        </div>
        <i class="bi bi-chevron-right rp-resv-stat-arrow" aria-hidden="true"></i>
    </a>
</div>

<div class="rp-resv-table-card d-none d-lg-block">
    <div class="table-responsive">
        <table class="table align-middle rp-resv-table mb-0">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Guest Name</th>
                    <th>Room / Cottage</th>
                    <th>Arrival</th>
                    <th>Departure</th>
                    <th>Guests</th>
                    <th>Payment Status</th>
                    <th>Reservation Status</th>
                    <th>Source</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $statusValue = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
                        $guestName = $booking->guest_name ?? $booking->guest?->user?->name ?? '—';
                        $guestPhone = $booking->contact_number ?? $booking->guest?->user?->phone ?? $booking->guest?->contact_number ?? '—';
                        $paid = (float) $booking->paid_amount;
                        $remaining = (float) $booking->remaining_balance;
                        $paymentKey = $remaining <= 0.009 ? 'paid' : ($paid > 0.009 ? 'pending' : 'unpaid');
                        $paymentLabel = match ($paymentKey) {
                            'paid' => 'Paid',
                            'pending' => 'Partially paid',
                            default => 'Unpaid',
                        };
                        $awaitingStay = in_array($statusValue, ['pending', 'approved'], true);
                        if ($awaitingStay && $booking->isFullyPaid()) {
                            $resStatus = 'ready';
                            $resLabel = 'Ready';
                        } elseif ($awaitingStay) {
                            $resStatus = 'confirmed';
                            $resLabel = 'Confirmed';
                        } elseif ($statusValue === 'checked_in') {
                            $resStatus = 'checked_in';
                            $resLabel = 'Checked-in';
                        } elseif ($statusValue === 'checked_out') {
                            $resStatus = 'checked_out';
                            $resLabel = 'Checked-out';
                        } else {
                            $resStatus = $statusValue;
                            $resLabel = str_replace('_', ' ', ucwords($statusValue, '_'));
                        }
                        $checkOutDue = $statusValue === 'checked_in' && $booking->checkOutDue();
                        $isEarlyCheckOut = $statusValue === 'checked_in' && $booking->isEarlyCheckOut();
                        $hasOpenIncidents = $booking->hasOpenIncidents();
                        $canCollectBalance = $remaining > 0.009 && in_array($statusValue, ['pending', 'approved', 'checked_in'], true);
                        $canCheckIn = $statusValue === 'approved' && $booking->isFullyPaid() && ! $booking->checkIn && $booking->checkInWindowOpen();
                        $canCheckOut = $statusValue === 'checked_in' && ! $canCollectBalance;
                        $checkInDisplay = $booking->checkIn?->checked_in_at;
                        $checkOutDisplay = $booking->checkOut?->checked_out_at;
                    @endphp
                    <tr>
                        <td><span class="rp-resv-id">{{ $booking->short_number }}</span></td>
                        <td>
                            <div class="rp-resv-guest-name">{{ $guestName }}</div>
                            <div class="rp-resv-guest-phone">{{ $guestPhone }}</div>
                        </td>
                        <td>
                            <div class="rp-resv-room-name">{{ $booking->accommodation?->name ?? '—' }}</div>
                            <div class="rp-resv-room-code">{{ $booking->accommodation?->number ?? '—' }}</div>
                        </td>
                        <td>
                            <div class="rp-resv-date-main">{{ $booking->check_in_date->format('M j, Y') }}</div>
                            <div class="rp-resv-date-sub">Planned {{ $checkInTime }}</div>
                            @if($checkInDisplay)
                                <div class="rp-resv-date-actual">
                                    Arrived {{ $checkInDisplay->format('M j, g:i A') }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="rp-resv-date-main">{{ $booking->check_out_date->format('M j, Y') }}</div>
                            <div class="rp-resv-date-sub">
                                Planned {{ $checkOutTime }}
                                @if($checkOutDue && ! $checkOutDisplay)
                                    · <span class="rp-resv-due">Due now</span>
                                @endif
                            </div>
                            @if($checkOutDisplay)
                                <div class="rp-resv-date-actual">
                                    Left {{ $checkOutDisplay->format('M j, g:i A') }}
                                </div>
                            @endif
                        </td>
                        <td>{{ $booking->number_of_guests ?? (($booking->adults ?? 0) + ($booking->children ?? 0)) ?: '—' }}</td>
                        <td><span class="rp-resv-pill is-pay-{{ $paymentKey }}">{{ $paymentLabel }}</span></td>
                        <td>
                            <span class="rp-resv-pill is-status-{{ $resStatus }}">{{ $resLabel }}</span>
                            @if($checkOutDue)
                                <span class="rp-resv-pill is-due ms-1">Due now</span>
                            @endif
                        </td>
                        <td>{{ $booking->is_walk_in ? 'Walk-in' : 'Online' }}</td>
                        <td class="text-end text-nowrap">
                            <div class="rp-resv-actions">
                                @if($canCollectBalance)
                                    <button type="button" class="btn btn-sm rp-resv-btn-primary" data-bs-toggle="modal" data-bs-target="#rpCollectModal{{ $booking->id }}">Collect balance</button>
                                @endif
                                @if($canCheckIn)
                                    <button type="button" class="btn btn-sm rp-resv-btn-primary" data-bs-toggle="modal" data-bs-target="#rpCheckInModal{{ $booking->id }}">Check-in</button>
                                @elseif($canCheckOut)
                                    <button type="button" class="btn btn-sm rp-resv-btn-outline" data-bs-toggle="modal" data-bs-target="#rpCheckOutModal{{ $booking->id }}">{{ $checkOutDue ? 'Check-out' : 'Early check-out' }}</button>
                                @endif
                                <button type="button" class="btn btn-sm rp-resv-btn-outline" data-bs-toggle="modal" data-bs-target="#rpResvViewModal{{ $booking->id }}">View</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-muted py-4 text-center">
                            @if($tab === 'logs')
                                No check-in or check-out logs yet.
                            @elseif($tab === 'check_out')
                                No guests currently in-house. Completed check-outs appear under Logs.
                            @elseif($tab === 'walk_in')
                                No fully paid walk-ins today.
                            @else
                                No reservations match this filter.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->total() > 0)
        <div class="rp-resv-footer">
            <div class="rp-resv-footer-meta">
                Showing {{ $bookings->firstItem() }}-{{ $bookings->lastItem() }} of {{ $bookings->total() }} {{ $tab === 'logs' ? 'logs' : 'reservations' }}
            </div>
            <div class="rp-resv-pagination">
                @if($bookings->onFirstPage())
                    <span class="rp-resv-page is-disabled"><i class="bi bi-chevron-left"></i></span>
                @else
                    <a href="{{ $bookings->previousPageUrl() }}" class="rp-resv-page"><i class="bi bi-chevron-left"></i></a>
                @endif
                @for($page = 1; $page <= $bookings->lastPage(); $page++)
                    @if($page === $bookings->currentPage())
                        <span class="rp-resv-page is-active">{{ $page }}</span>
                    @else
                        <a href="{{ $bookings->url($page) }}" class="rp-resv-page">{{ $page }}</a>
                    @endif
                @endfor
                @if($bookings->hasMorePages())
                    <a href="{{ $bookings->nextPageUrl() }}" class="rp-resv-page"><i class="bi bi-chevron-right"></i></a>
                @else
                    <span class="rp-resv-page is-disabled"><i class="bi bi-chevron-right"></i></span>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Mobile cards --}}
<div class="d-lg-none">
    @forelse($bookings as $booking)
        @php
            $statusValue = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
            $guestName = $booking->guest_name ?? $booking->guest?->user?->name ?? '—';
            $paid = (float) $booking->paid_amount;
            $remaining = (float) $booking->remaining_balance;
            $paymentKey = $remaining <= 0.009 ? 'paid' : ($paid > 0.009 ? 'pending' : 'unpaid');
            $checkOutDue = $statusValue === 'checked_in' && $booking->checkOutDue();
            $canCollectBalance = $remaining > 0.009 && in_array($statusValue, ['pending', 'approved', 'checked_in'], true);
            $canCheckIn = $statusValue === 'approved' && $booking->isFullyPaid() && ! $booking->checkIn && $booking->checkInWindowOpen();
            $canCheckOut = $statusValue === 'checked_in' && ! $canCollectBalance;
        @endphp
        <div class="rp-resv-mobile-card">
            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                <div>
                    <div class="rp-resv-id">{{ $booking->short_number }}</div>
                    <div class="rp-resv-guest-name">{{ $guestName }}</div>
                </div>
                <div class="d-flex flex-column align-items-end gap-1">
                    <span class="rp-resv-pill is-pay-{{ $paymentKey }}">{{ $paymentKey === 'paid' ? 'Paid' : ($paymentKey === 'pending' ? 'Partially paid' : 'Unpaid') }}</span>
                    @if($checkOutDue)
                        <span class="rp-resv-pill is-due">Due now</span>
                    @endif
                </div>
            </div>
            <div class="rp-resv-room-name mb-1">{{ $booking->accommodation?->name ?? '—' }}</div>
            <div class="rp-resv-date-sub mb-1">
                Planned {{ $booking->check_in_date->format('M j') }} → {{ $booking->check_out_date->format('M j, Y') }}
                · {{ $booking->is_walk_in ? 'Walk-in' : 'Online' }}
            </div>
            @if($booking->checkIn || $booking->checkOut)
                <div class="rp-resv-log-mini mb-3">
                    @if($booking->checkIn)
                        <div>Arrived {{ $booking->checkIn->checked_in_at?->format('M j, g:i A') }}</div>
                    @endif
                    @if($booking->checkOut)
                        <div>Left {{ $booking->checkOut->checked_out_at?->format('M j, g:i A') }}</div>
                    @endif
                </div>
            @else
                <div class="mb-3"></div>
            @endif
            <div class="rp-resv-actions">
                @if($canCollectBalance)
                    <button type="button" class="btn btn-sm rp-resv-btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#rpCollectModal{{ $booking->id }}">Collect balance</button>
                @endif
                @if($canCheckIn)
                    <button type="button" class="btn btn-sm rp-resv-btn-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#rpCheckInModal{{ $booking->id }}">Check-in</button>
                @elseif($canCheckOut)
                    <button type="button" class="btn btn-sm rp-resv-btn-outline flex-grow-1" data-bs-toggle="modal" data-bs-target="#rpCheckOutModal{{ $booking->id }}">{{ $checkOutDue ? 'Check-out' : 'Early check-out' }}</button>
                @endif
                <button type="button" class="btn btn-sm rp-resv-btn-outline flex-grow-1" data-bs-toggle="modal" data-bs-target="#rpResvViewModal{{ $booking->id }}">View</button>
            </div>
        </div>
    @empty
        <div class="rp-card text-muted">
            @if($tab === 'logs')
                No check-in or check-out logs yet.
            @elseif($tab === 'check_out')
                No guests currently in-house. Completed check-outs appear under Logs.
            @elseif($tab === 'walk_in')
                No fully paid walk-ins today.
            @else
                No reservations match this filter.
            @endif
        </div>
    @endforelse

    @if($bookings->hasPages())
        <div class="rp-resv-pagination mt-3 justify-content-center">
            @if(! $bookings->onFirstPage())
                <a href="{{ $bookings->previousPageUrl() }}" class="rp-resv-page"><i class="bi bi-chevron-left"></i></a>
            @endif
            <span class="rp-resv-footer-meta align-self-center px-2">Page {{ $bookings->currentPage() }} / {{ $bookings->lastPage() }}</span>
            @if($bookings->hasMorePages())
                <a href="{{ $bookings->nextPageUrl() }}" class="rp-resv-page"><i class="bi bi-chevron-right"></i></a>
            @endif
        </div>
    @endif
</div>
{{-- Action modals --}}
@foreach($bookings as $booking)
    @php
        $statusValue = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
        $guestName = $booking->guest_name ?? $booking->guest?->user?->name ?? '—';
        $guestPhone = $booking->contact_number ?? $booking->guest?->user?->phone ?? $booking->guest?->contact_number ?? '—';
        $guestEmail = $booking->email ?? $booking->guest?->user?->email ?? '—';
        $checkOutDue = $statusValue === 'checked_in' && $booking->checkOutDue();
        $isEarlyCheckOut = $statusValue === 'checked_in' && $booking->isEarlyCheckOut();
        $hasOpenIncidents = $booking->hasOpenIncidents();
        $bookingTotal = (float) $booking->total_amount;
        $bookingPaid = (float) $booking->paid_amount;
        $remainingBalance = (float) $booking->remaining_balance;
        $canCollectBalance = $remainingBalance > 0.009 && in_array($statusValue, ['pending', 'approved', 'checked_in'], true);
        $canCheckIn = $statusValue === 'approved' && $booking->isFullyPaid() && ! $booking->checkIn && $booking->checkInWindowOpen();
        $canCheckOut = $statusValue === 'checked_in' && ! $canCollectBalance;
        $payment = $booking->payments->first();
        $methodLabel = $payment
            ? str_replace('_', ' ', ucfirst($payment->payment_method instanceof \BackedEnum ? $payment->payment_method->value : (string) $payment->payment_method))
            : '—';
        $paymentStatus = $remainingBalance <= 0.009 ? 'paid' : ($bookingPaid > 0.009 ? 'pending' : 'unpaid');
        $paymentLabel = match ($paymentStatus) {
            'paid' => 'Paid',
            'pending' => 'Partially paid',
            default => 'Unpaid',
        };
        $awaitingStay = in_array($statusValue, ['pending', 'approved'], true);
        if ($awaitingStay && $booking->isFullyPaid()) {
            $resLabel = 'Ready for check-in';
        } elseif ($awaitingStay) {
            $resLabel = 'Confirmed';
        } elseif ($statusValue === 'checked_in') {
            $resLabel = 'Checked-in';
        } elseif ($statusValue === 'checked_out') {
            $resLabel = 'Checked-out';
        } else {
            $resLabel = str_replace('_', ' ', ucwords($statusValue, '_'));
        }
    @endphp

    <div class="modal fade" id="rpResvViewModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rp-admin-modal">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 mb-0">Reservation {{ $booking->short_number }}</h2>
                        <div class="text-muted small">{{ $resLabel }} · {{ $booking->is_walk_in ? 'Walk-in' : 'Online' }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(session('success') && (string) session('open_view') === (string) $booking->id)
                        <div class="alert alert-success border-0 shadow-sm mb-3" role="status">
                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>{{ session('success') }}
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="rp-resv-action-summary h-100">
                                <div class="text-muted small mb-1">Guest</div>
                                <div class="fw-semibold">{{ $guestName }}</div>
                                <div class="text-muted small">{{ $guestPhone }}</div>
                                <div class="text-muted small">{{ $guestEmail }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="rp-resv-action-summary h-100">
                                <div class="text-muted small mb-1">Stay</div>
                                <div class="fw-semibold">{{ $booking->accommodation?->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $booking->accommodation?->number ?? '—' }}</div>
                                <div class="text-muted small">{{ $booking->check_in_date->format('M j, Y') }} → {{ $booking->check_out_date->format('M j, Y') }}</div>
                                <div class="text-muted small">Guests: {{ $booking->number_of_guests ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Total</div>
                            <div class="fw-semibold">₱{{ number_format($bookingTotal, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Paid</div>
                            <div class="fw-semibold">₱{{ number_format($bookingPaid, 2) }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Balance</div>
                            <div class="fw-semibold">₱{{ number_format($remainingBalance, 2) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Payment status</div>
                            <div><span class="rp-resv-pill is-pay-{{ $paymentStatus }}">{{ $paymentLabel }}</span></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Latest payment</div>
                            <div>{{ $payment ? '₱'.number_format((float) $payment->amount, 2).' · '.$methodLabel : 'No payment yet' }}</div>
                            @if($payment?->reference_number)
                                <div class="text-muted small">Ref: {{ $payment->reference_number }}</div>
                            @endif
                        </div>
                        @if($booking->promo_code || ((float) $booking->discount_amount) > 0)
                            <div class="col-12">
                                <div class="text-muted small">Promo</div>
                                <div><code>{{ $booking->promo_code ?: ($booking->promo?->code ?? '—') }}</code></div>
                            </div>
                        @endif
                        @if($booking->special_requests)
                            <div class="col-12">
                                <div class="text-muted small">Special requests</div>
                                <div>{{ $booking->special_requests }}</div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="text-muted small mb-2">Check-in / Check-out log</div>
                            <div class="rp-resv-log-list">
                                @if($booking->checkIn)
                                    <div class="rp-resv-log-item">
                                        <div class="rp-resv-log-item__head">
                                            <span class="rp-resv-pill is-status-checked_in">Check-in</span>
                                            <span class="rp-resv-log-item__when">{{ $booking->checkIn->checked_in_at?->format('M j, Y · g:i A') ?? '—' }}</span>
                                        </div>
                                        <div class="rp-resv-log-item__meta">By {{ $booking->checkIn->staff?->name ?? 'Staff' }}</div>
                                        @if($booking->checkIn->notes)
                                            <div class="rp-resv-log-item__notes">{{ $booking->checkIn->notes }}</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="rp-resv-log-item is-empty">No check-in recorded yet.</div>
                                @endif
                                @if($booking->checkOut)
                                    <div class="rp-resv-log-item">
                                        <div class="rp-resv-log-item__head">
                                            <span class="rp-resv-pill is-status-checked_out">Check-out</span>
                                            <span class="rp-resv-log-item__when">{{ $booking->checkOut->checked_out_at?->format('M j, Y · g:i A') ?? '—' }}</span>
                                        </div>
                                        <div class="rp-resv-log-item__meta">
                                            By {{ $booking->checkOut->staff?->name ?? 'Staff' }}
                                            @if(((float) $booking->checkOut->additional_charges) > 0)
                                                · Extra ₱{{ number_format((float) $booking->checkOut->additional_charges, 2) }}
                                            @endif
                                        </div>
                                        @if($booking->checkOut->notes)
                                            <div class="rp-resv-log-item__notes">{{ $booking->checkOut->notes }}</div>
                                        @endif
                                    </div>
                                @else
                                    <div class="rp-resv-log-item is-empty">No check-out recorded yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if($canCollectBalance || $canCheckIn || $canCheckOut)
                <div class="modal-footer">
                    @if($canCollectBalance)
                        <button type="button" class="btn btn-rp-primary" data-rp-open-after-hide="#rpCollectModal{{ $booking->id }}">Collect balance</button>
                    @endif
                    @if($canCheckIn)
                        <button type="button" class="btn btn-rp-primary" data-rp-open-after-hide="#rpCheckInModal{{ $booking->id }}">Check-in</button>
                    @elseif($canCheckOut)
                        <button type="button" class="btn btn-rp-primary" data-rp-open-after-hide="#rpCheckOutModal{{ $booking->id }}">{{ $checkOutDue ? 'Check-out' : 'Early check-out' }}</button>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($canCollectBalance)
    <div class="modal fade" id="rpCollectModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rp-admin-modal">
                <form method="POST" action="{{ route('front_desk.payments.collect', $booking) }}" data-rp-payment-form>
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5">Collect balance</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rp-resv-action-summary mb-3">
                            <div><strong>{{ $guestName }}</strong></div>
                            <div class="text-muted small">{{ $booking->short_number }} · {{ $booking->accommodation?->name ?? '—' }}</div>
                            <div class="fw-semibold mt-1">Balance due: ₱{{ number_format($remainingBalance, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" max="{{ number_format($remainingBalance, 2, '.', '') }}" name="amount" class="form-control" value="{{ number_format($remainingBalance, 2, '.', '') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment method</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                            </select>
                        </div>
                        <div class="mb-3" data-rp-pay-ref-wrap hidden>
                            <label class="form-label">Reference number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Required for GCash">
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Front desk collection note…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rp-primary">Record payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($canCheckIn)
    <div class="modal fade" id="rpCheckInModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rp-admin-modal">
                <form method="POST" action="{{ route('front_desk.checkins.store', $booking) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5">Confirm check-in</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rp-resv-action-summary mb-3">
                            <div class="text-muted small mb-1">Guest</div>
                            <div><strong>{{ $guestName }}</strong></div>
                            <div class="text-muted small mb-2">{{ $guestPhone }}</div>
                            <div class="text-muted small mb-1">Room / cottage</div>
                            <div class="fw-semibold">{{ $booking->accommodation?->name ?? '—' }}</div>
                            <div class="text-muted small mb-2">{{ $booking->short_number }} · {{ $booking->check_in_date->format('M j, Y') }} → {{ $booking->check_out_date->format('M j, Y') }}</div>
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <span class="text-muted small">Balance</span>
                                <span class="fw-semibold text-success">₱0.00 · Paid</span>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes (optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Room keys, special notes…"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rp-primary">Confirm Check-in</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($canCheckOut)
    <div class="modal fade" id="rpCheckOutModal{{ $booking->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rp-admin-modal">
                <form method="POST" action="{{ route('front_desk.checkouts.store', $booking) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5">{{ $isEarlyCheckOut ? 'Early check-out' : 'Confirm check-out' }}</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="rp-resv-action-summary mb-3">
                            <div><strong>{{ $guestName }}</strong></div>
                            <div class="text-muted small">{{ $booking->short_number }} · {{ $booking->accommodation?->name ?? '—' }}</div>
                            <div class="text-muted small">Scheduled out: {{ $booking->check_out_date->format('M j, Y') }} · {{ $checkOutTime }}</div>
                            <div class="fw-semibold mt-1">Balance: ₱{{ number_format($remainingBalance, 2) }}</div>
                            @if($checkOutDue)
                                <span class="rp-resv-pill is-due mt-2">Due now</span>
                            @elseif($isEarlyCheckOut)
                                <span class="rp-resv-pill is-early mt-2">Early check-out</span>
                            @endif
                        </div>
                        @if($hasOpenIncidents)
                            <div class="alert alert-warning py-2">
                                This stay has open incident reports. Resolve them under Incident Reports before check-out.
                            </div>
                        @endif
                        <div class="mb-3">
                            <label class="form-label">Additional charges</label>
                            <input type="number" step="0.01" min="0" name="additional_charges" class="form-control" value="0" @disabled($hasOpenIncidents)>
                            <div class="form-text">If you add extras, collect that balance first, then check out.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">{{ $isEarlyCheckOut ? 'Reason for early check-out' : 'Notes (optional)' }}</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="{{ $isEarlyCheckOut ? 'Required — why the guest is leaving early…' : 'Damages, late checkout, etc.' }}" @required($isEarlyCheckOut) @disabled($hasOpenIncidents)></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-rp-primary" @disabled($hasOpenIncidents)>Confirm Check-out</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

{{-- Walk-in modal --}}
<div class="modal fade" id="rpWalkInModal" tabindex="-1" aria-labelledby="rpWalkInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content rp-admin-modal rp-walkin-modal">
            <form method="POST" action="{{ route('front_desk.walkins.store') }}" data-rp-walkin-form data-rp-availability-form data-occupied-url="{{ url('/accommodations') }}/__ACCOMMODATION__/occupied-dates" data-rp-no-auto-submit novalidate>
                @csrf
                <input type="hidden" name="_modal" value="walkin">
                <div class="modal-header rp-walkin-modal-header">
                    <div class="rp-walkin-modal-heading">
                        <span class="rp-walkin-modal-icon" aria-hidden="true"><i class="bi bi-calendar2-plus"></i></span>
                        <div>
                            <h2 class="modal-title h5 mb-0" id="rpWalkInModalLabel">New Walk-in Booking</h2>
                            <p class="rp-walkin-modal-sub mb-0">Create a new reservation for a walk-in guest.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if(old('_modal') === 'walkin' && $errors->any())
                        <div class="alert alert-danger border-0 shadow-sm mb-3 rp-walkin-modal-errors" role="alert">
                            <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>Please fix the following</div>
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-person me-1" aria-hidden="true"></i>Guest name</label>
                            <input type="text" name="guest_name" class="form-control @error('guest_name') is-invalid @enderror" value="{{ old('_modal') === 'walkin' ? old('guest_name') : '' }}" required>
                            @error('guest_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bi bi-telephone me-1" aria-hidden="true"></i>Contact number</label>
                            <input type="text" name="contact_number" class="form-control @error('contact_number') is-invalid @enderror" value="{{ old('_modal') === 'walkin' ? old('contact_number') : '' }}" required>
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label"><i class="bi bi-house-door me-1" aria-hidden="true"></i>Accommodation</label>
                            <select name="accommodation_id" class="form-select" data-rp-capacity-select required>
                                @foreach($accommodations as $item)
                                    <option value="{{ $item->id }}" data-capacity="{{ $item->capacity }}" data-rate="{{ $item->rate }}" @selected(old('_modal') === 'walkin' && old('accommodation_id') == $item->id)>
                                        {{ $item->name }} — ₱{{ number_format($item->rate, 2) }} (cap {{ $item->capacity }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label"><i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i>Check-in</label>
                            <div class="rp-avail-input-wrap" data-rp-open-calendar>
                                <input type="text" class="rp-avail-input" value="{{ old('_modal') === 'walkin' && old('check_in_date') ? \Carbon\Carbon::parse(old('check_in_date'))->format('m/d/Y') : now()->format('m/d/Y') }}" placeholder="Select date" readonly data-rp-date-display="check_in">
                                <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                            </div>
                            <input type="hidden" name="check_in_date" value="{{ old('_modal') === 'walkin' ? old('check_in_date', now()->toDateString()) : now()->toDateString() }}" data-stay-check-in required>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label"><i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>Check-out</label>
                            <div class="rp-avail-input-wrap" data-rp-open-calendar>
                                <input type="text" class="rp-avail-input" value="{{ old('_modal') === 'walkin' && old('check_out_date') ? \Carbon\Carbon::parse(old('check_out_date'))->format('m/d/Y') : now()->addDay()->format('m/d/Y') }}" placeholder="Select date" readonly data-rp-date-display="check_out">
                                <i class="bi bi-calendar3 rp-avail-input-icon"></i>
                            </div>
                            <input type="hidden" name="check_out_date" value="{{ old('_modal') === 'walkin' ? old('check_out_date', now()->addDay()->toDateString()) : now()->addDay()->toDateString() }}" data-stay-check-out required>
                        </div>
                        <div class="col-12">
                            <div class="rp-walkin-total-banner">
                                <div>
                                    <div class="rp-walkin-total-label">Full stay total</div>
                                    <div class="rp-walkin-total-value" data-rp-walkin-total>—</div>
                                </div>
                                <div class="rp-walkin-total-hint">room rate × nights · must pay in full · Cash + GCash split allowed</div>
                            </div>
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label">Adults</label>
                            <input type="number" min="1" name="adults" class="form-control" value="{{ old('_modal') === 'walkin' ? old('adults', 1) : 1 }}" data-rp-guest-adults required>
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label">Children</label>
                            <input type="number" min="0" name="children" class="form-control" value="{{ old('_modal') === 'walkin' ? old('children', 0) : 0 }}" data-rp-guest-children>
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label">Guests</label>
                            <input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('_modal') === 'walkin' ? old('number_of_guests', 1) : 1 }}" data-rp-guest-total readonly required>
                            <div class="text-danger small d-none" data-rp-capacity-error>Exceeds capacity.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label"><i class="bi bi-chat-left-text me-1" aria-hidden="true"></i>Special requests</label>
                            <textarea name="special_requests" class="form-control" rows="2" placeholder="Optional notes for the stay…">{{ old('_modal') === 'walkin' ? old('special_requests') : '' }}</textarea>
                        </div>
                        @include('partials.walkin-payments')
                    </div>
                </div>
                <div class="modal-footer rp-walkin-modal-footer">
                    <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-rp-primary">Create Walk-in Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('partials.availability-calendar')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-rp-resv-date]').forEach((wrap) => {
        const toggle = wrap.querySelector('[data-rp-resv-date-toggle]');
        const panel = wrap.querySelector('[data-rp-resv-date-panel]');
        if (!toggle || !panel) return;
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            panel.hidden = !panel.hidden;
        });
        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) panel.hidden = true;
        });
    });

    const openModal = @json(session('open_modal') ?: old('_modal'));
    const openCheckout = @json(session('open_checkout'));
    const openView = @json(session('open_view'));
    let targetId = null;
    if (openModal === 'walkin') targetId = 'rpWalkInModal';
    else if (openCheckout) targetId = `rpCheckOutModal${openCheckout}`;
    else if (openView) targetId = `rpResvViewModal${openView}`;
    const el = targetId ? document.getElementById(targetId) : null;
    if (el && window.bootstrap?.Modal) {
        const modal = window.bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
        // Keep validation errors in view when the walk-in modal reopens after a failed submit.
        if (openModal === 'walkin') {
            el.addEventListener('shown.bs.modal', () => {
                const body = el.querySelector('.modal-body');
                const errors = el.querySelector('.rp-walkin-modal-errors');
                if (body) body.scrollTop = 0;
                errors?.focus?.();
            }, { once: true });
        }
    }

    // Bootstrap often fails when dismiss + open run on the same click; chain them.
    document.querySelectorAll('[data-rp-open-after-hide]').forEach((button) => {
        button.addEventListener('click', () => {
            const nextSelector = button.getAttribute('data-rp-open-after-hide');
            const nextEl = nextSelector ? document.querySelector(nextSelector) : null;
            const currentModalEl = button.closest('.modal');
            if (!nextEl || !window.bootstrap?.Modal) return;

            const openNext = () => {
                window.bootstrap.Modal.getOrCreateInstance(nextEl).show();
            };

            if (!currentModalEl || !currentModalEl.classList.contains('show')) {
                openNext();
                return;
            }

            currentModalEl.addEventListener('hidden.bs.modal', openNext, { once: true });
            window.bootstrap.Modal.getOrCreateInstance(currentModalEl).hide();
        });
    });
});
</script>
@endpush
