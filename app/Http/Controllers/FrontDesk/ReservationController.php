<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\RejectBookingRequest;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Services\BookingService;
use App\Support\ListFilters;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    public function index(Request $request): View
    {
        $tab = (string) $request->input('tab', 'all');
        if (! in_array($tab, ['all', 'check_in', 'check_out', 'walk_in', 'logs'], true)) {
            $tab = 'all';
        }

        $dateFrom = $this->parseDate($request->input('from'));
        $dateTo = $this->parseDate($request->input('to'));
        $today = today()->toDateString();

        $with = [
            'guest.user',
            'accommodation' => fn ($q) => $q->withTrashed(),
            'checkIn.staff',
            'checkOut.staff',
            'payments' => fn ($q) => $q->latest('payment_date'),
            'promo',
        ];

        $openIncidentStatuses = [
            \App\Enums\IncidentStatus::Pending,
            \App\Enums\IncidentStatus::Verified,
            \App\Enums\IncidentStatus::InProgress,
        ];

        $statsBase = Booking::query()->frontDeskReservations();

        $stats = [
            'total' => (clone $statsBase)->count(),
            'check_ins_today' => (clone $statsBase)
                ->whereDate('check_in_date', $today)
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->count(),
            'check_ins_today_pending' => (clone $statsBase)
                ->whereDate('check_in_date', $today)
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->where('remaining_balance', '>', 0)
                ->count(),
            'check_ins_ready' => (clone $statsBase)->readyForCheckIn()->count(),
            'check_outs_today' => (clone $statsBase)
                ->where('status', BookingStatus::CheckedIn)
                ->whereDate('check_out_date', $today)
                ->count(),
            'check_outs_today_due' => (clone $statsBase)
                ->where('status', BookingStatus::CheckedIn)
                ->checkOutDue()
                ->count(),
            'walk_ins_today' => (clone $statsBase)
                ->where('is_walk_in', true)
                ->where(function ($query) use ($today) {
                    $query->whereDate('created_at', $today)
                        ->orWhereDate('check_in_date', $today);
                })
                // Walk-ins are always full-pay; exclude leftover partial balances.
                ->where(function ($query) {
                    $query->where('status', BookingStatus::CheckedIn)
                        ->orWhere('remaining_balance', '<=', 0.009);
                })
                ->count(),
        ];

        $withCount = ['incidentReports as open_incidents_count' => function ($q) use ($openIncidentStatuses) {
            $q->whereIn('status', $openIncidentStatuses);
        }];

        if ($tab === 'logs') {
            $query = Booking::query()
                ->with($with)
                ->withCount($withCount)
                ->where(function ($builder) {
                    $builder->whereHas('checkIn')->orWhereHas('checkOut');
                });
        } else {
            $query = Booking::query()
                ->with($with)
                ->withCount($withCount)
                ->frontDeskReservations();

            if ($tab === 'check_in') {
                // Ready arrivals only (fully paid, arrival day+, not yet checked in).
                $query->readyForCheckIn();
            } elseif ($tab === 'check_out') {
                // Action queue: guests still in-house (completed check-outs go to Logs).
                $query->where('status', BookingStatus::CheckedIn);
            } elseif ($tab === 'walk_in') {
                // Same scope as the Walk-ins Today card: today's walk-ins, fully paid only.
                $query->where('is_walk_in', true)
                    ->where(function ($builder) use ($today) {
                        $builder->whereDate('created_at', $today)
                            ->orWhereDate('check_in_date', $today);
                    })
                    ->where(function ($builder) {
                        $builder->where('status', BookingStatus::CheckedIn)
                            ->orWhere('remaining_balance', '<=', 0.009);
                    });
            }
        }

        if ($dateFrom && $dateTo) {
            $query->where(function ($builder) use ($dateFrom, $dateTo, $tab) {
                if ($tab === 'logs') {
                    $builder->whereHas('checkIn', function ($checkIn) use ($dateFrom, $dateTo) {
                        $checkIn->whereDate('checked_in_at', '>=', $dateFrom)
                            ->whereDate('checked_in_at', '<=', $dateTo);
                    })->orWhereHas('checkOut', function ($checkOut) use ($dateFrom, $dateTo) {
                        $checkOut->whereDate('checked_out_at', '>=', $dateFrom)
                            ->whereDate('checked_out_at', '<=', $dateTo);
                    });

                    return;
                }

                $builder->whereBetween('check_in_date', [$dateFrom, $dateTo])
                    ->orWhereBetween('check_out_date', [$dateFrom, $dateTo])
                    ->orWhere(function ($stay) use ($dateFrom, $dateTo) {
                        $stay->whereDate('check_in_date', '<=', $dateFrom)
                            ->whereDate('check_out_date', '>=', $dateTo);
                    });
            });
        } elseif ($dateFrom) {
            if ($tab === 'logs') {
                $query->where(function ($builder) use ($dateFrom) {
                    $builder->whereHas('checkIn', fn ($q) => $q->whereDate('checked_in_at', '>=', $dateFrom))
                        ->orWhereHas('checkOut', fn ($q) => $q->whereDate('checked_out_at', '>=', $dateFrom));
                });
            } else {
                $query->whereDate('check_out_date', '>=', $dateFrom);
            }
        } elseif ($dateTo) {
            if ($tab === 'logs') {
                $query->where(function ($builder) use ($dateTo) {
                    $builder->whereHas('checkIn', fn ($q) => $q->whereDate('checked_in_at', '<=', $dateTo))
                        ->orWhereHas('checkOut', fn ($q) => $q->whereDate('checked_out_at', '<=', $dateTo));
                });
            } else {
                $query->whereDate('check_in_date', '<=', $dateTo);
            }
        }

        if ($request->filled('q')) {
            ListFilters::applyBookingSearch($query, $request->input('q'));
        }

        if ($tab === 'logs') {
            $bookings = $query
                ->orderByDesc(DB::raw(
                    'COALESCE((SELECT checked_out_at FROM check_outs WHERE check_outs.booking_id = bookings.id LIMIT 1), (SELECT checked_in_at FROM check_ins WHERE check_ins.booking_id = bookings.id LIMIT 1), bookings.updated_at)'
                ))
                ->paginate(8)
                ->withQueryString();
        } else {
            $bookings = $query->latest()->paginate(8)->withQueryString();
        }

        $checkInTime = Carbon::createFromFormat('H:i', (string) config('resort.check_in_time', '14:00'))->format('g:i A');
        $checkOutTime = Carbon::createFromFormat('H:i', (string) config('resort.check_out_time', '12:00'))->format('g:i A');

        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'inactive'])
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'rate', 'status']);

        return view('front_desk.reservations.index', compact(
            'bookings',
            'stats',
            'tab',
            'dateFrom',
            'dateTo',
            'checkInTime',
            'checkOutTime',
            'accommodations'
        ));
    }

    public function show(Booking $booking): View|RedirectResponse
    {
        if (in_array($booking->status, [BookingStatus::Rejected, BookingStatus::Cancelled], true)) {
            return redirect()
                ->route('front_desk.reservations.index')
                ->with('success', 'This reservation is no longer available.');
        }

        if (! request()->boolean('page')) {
            $params = [];
            if ($booking->status === BookingStatus::CheckedOut) {
                $params['tab'] = 'logs';
            }

            return redirect()
                ->route('front_desk.reservations.index', $params)
                ->with('open_view', $booking->id);
        }

        $booking->load([
            'guest.user',
            'accommodation' => fn ($q) => $q->withTrashed(),
            'items',
            'payments.processor',
            'payments.verifier',
            'checkIn',
            'checkOut',
            'promo',
        ]);

        return view('front_desk.reservations.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('approve', $booking);
        $this->bookingService->approve($booking, $request->user());

        return back()->with('success', 'Reservation approved.');
    }

    public function reject(RejectBookingRequest $request, Booking $booking): RedirectResponse
    {
        $this->authorize('reject', $booking);
        $this->bookingService->reject($booking, $request->user(), $request->validated('rejection_reason'));

        return redirect()
            ->route('front_desk.reservations.index')
            ->with('success', 'Reservation rejected and removed from the queue.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        $this->bookingService->cancel($booking, $request->user());

        return back()->with('success', 'Reservation cancelled.');
    }

    private function parseDate(mixed $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
