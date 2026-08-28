<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccommodationBrowseController extends Controller
{
    public function __construct(protected AvailabilityService $availabilityService)
    {
    }

    public function index(Request $request): View
    {
        $query = Accommodation::query()
            ->with(['type', 'amenities'])
            ->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('accommodation_type_id', $request->integer('type'));
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->input('check_in');
            $checkOut = $request->input('check_out');

            $query->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereDoesntHave('bookings', function ($bookingQuery) use ($checkIn, $checkOut) {
                    $bookingQuery->whereIn('status', ['pending', 'approved', 'checked_in'])
                        ->where('check_in_date', '<', $checkOut)
                        ->where('check_out_date', '>', $checkIn);
                });
            })->whereNotIn('status', ['maintenance', 'inactive']);
        }

        $accommodations = $query->orderBy('name')->paginate(12);
        $types = AccommodationType::query()->orderBy('name')->get();

        return view('accommodations.browse', compact('accommodations', 'types'));
    }

    public function show(Request $request, Accommodation $accommodation): View
    {
        $accommodation->load(['type', 'amenities', 'pricing']);

        $available = null;
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $available = $this->availabilityService->isAvailable(
                $accommodation->id,
                $request->input('check_in'),
                $request->input('check_out')
            );
        }

        return view('accommodations.show', compact('accommodation', 'available'));
    }

    public function availability(Request $request, Accommodation $accommodation): View
    {
        $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        return $this->show($request, $accommodation);
    }
}
