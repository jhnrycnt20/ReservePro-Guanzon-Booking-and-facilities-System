<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationBrowseController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService)
    {
    }

    public function index(Request $request): View
    {
        if ($request->filled('check_in') || $request->filled('check_out')) {
            $request->validate([
                'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            ]);
        }

        $query = Accommodation::query()
            ->with(['type', 'amenities'])
            ->where('is_active', true)
            ->representatives();

        if ($request->filled('type')) {
            $query->where('accommodation_type_id', $request->integer('type'));
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            // Keep a family card if any sibling unit is free for the selected dates.
            $availableFamilyKeys = Accommodation::query()
                ->where('is_active', true)
                ->whereNotIn('status', ['maintenance', 'inactive'])
                ->whereDoesntHave('bookings', function ($bookingQuery) use ($checkIn, $checkOut) {
                    $bookingQuery->where('paid_amount', '>', 0)
                        ->whereIn('status', ['pending', 'approved', 'checked_in'])
                        ->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                })
                ->get(['id', 'number', 'accommodation_type_id', 'name'])
                ->map(fn (Accommodation $unit) => $unit->familyKey())
                ->unique()
                ->values()
                ->all();

            $keepIds = (clone $query)
                ->get(['id', 'number', 'accommodation_type_id', 'name'])
                ->filter(fn (Accommodation $unit) => in_array($unit->familyKey(), $availableFamilyKeys, true))
                ->pluck('id')
                ->all();

            $query->whereIn('id', $keepIds ?: [0]);
        }

        $accommodations = $query->orderBy('name')->paginate(12);
        $types = AccommodationType::query()->orderBy('name')->get();

        return view('accommodations.browse', compact('accommodations', 'types'));
    }

    public function show(Request $request, Accommodation $accommodation): View|RedirectResponse
    {
        if ($request->filled('check_in') || $request->filled('check_out')) {
            $request->validate([
                'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            ]);
        }

        $status = $accommodation->status instanceof \BackedEnum
            ? $accommodation->status->value
            : (string) $accommodation->status;

        if ($status === 'maintenance') {
            return redirect()
                ->route('accommodations.browse')
                ->with('error', 'Sorry, this room is under maintenance.');
        }

        if ($status === 'inactive' || ! $accommodation->is_active) {
            return redirect()
                ->route('accommodations.browse')
                ->with('error', 'Sorry, this room is currently unavailable.');
        }

        $accommodation->load(['type', 'amenities', 'pricing']);

        $units = $accommodation->siblingUnits()->map(fn (Accommodation $unit) => [
            'id' => $unit->id,
            'number' => $unit->number,
            'name' => $unit->name,
            'family' => $unit->familyKey(),
            'label' => $unit->unitNumber() !== null
                ? (string) $unit->unitNumber()
                : $unit->number,
            'occupied_url' => route('accommodations.occupied-dates', $unit),
            'availability_url' => route('accommodations.availability', $unit),
            'booking_url' => route('guest.bookings.create', [
                'accommodation_id' => $unit->id,
                'check_in' => $request->input('check_in'),
                'check_out' => $request->input('check_out'),
            ]),
        ])->values();

        $available = null;
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $available = $this->availabilityService->isAvailable(
                $accommodation->id,
                $request->input('check_in'),
                $request->input('check_out')
            );
        }

        return view('accommodations.show', compact('accommodation', 'available', 'units'));
    }

    public function availability(Request $request, Accommodation $accommodation): View
    {
        $request->validate([
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
        ]);

        return $this->show($request, $accommodation);
    }

    public function occupiedDates(Request $request, Accommodation $accommodation): JsonResponse
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $start = Carbon::create($request->integer('year'), $request->integer('month'), 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $occupied = $this->availabilityService->getOccupiedDates(
            $accommodation->id,
            $start,
            $end
        );

        return response()->json([
            'occupied' => $occupied,
            'year' => $start->year,
            'month' => $start->month,
        ]);
    }
}
