<?php

namespace App\Http\Controllers\Guest;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $guestId = $request->user()->guest?->id;

        $feedback = Feedback::query()
            ->where('guest_id', $guestId)
            ->with(['booking.accommodation', 'accommodation'])
            ->latest()
            ->paginate(15);

        return view('guest.feedback.index', compact('feedback'));
    }

    public function create(Request $request, ?Booking $booking = null): View
    {
        $guestId = $request->user()->guest?->id;

        if ($booking && $booking->exists) {
            abort_unless($booking->guest_id === $guestId, 403);
            abort_unless($booking->status === BookingStatus::CheckedOut, 422, 'Feedback allowed after check-out only.');
            $booking->load('accommodation');

            return view('guest.feedback.create', compact('booking'));
        }

        $bookings = Booking::query()
            ->where('guest_id', $guestId)
            ->where('status', BookingStatus::CheckedOut)
            ->whereDoesntHave('feedback')
            ->with('accommodation')
            ->latest()
            ->get();

        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('guest.feedback.create', compact('bookings', 'accommodations'));
    }

    public function store(Request $request, ?Booking $booking = null): RedirectResponse
    {
        $guest = $request->user()->guest;
        abort_unless($guest, 403);

        if ($booking && $booking->exists) {
            abort_unless($booking->guest_id === $guest->id, 403);
            abort_unless($booking->status === BookingStatus::CheckedOut, 422, 'Feedback allowed after check-out only.');

            $data = $request->validate([
                'rating' => ['required', 'integer', 'min:1', 'max:5'],
                'comment' => ['nullable', 'string', 'max:2000'],
            ]);

            Feedback::query()->updateOrCreate(
                ['booking_id' => $booking->id, 'guest_id' => $guest->id],
                [
                    'scope' => 'stay',
                    'accommodation_id' => $booking->accommodation_id,
                    'rating' => $data['rating'],
                    'comment' => $data['comment'] ?? null,
                ]
            );

            return redirect()
                ->route('guest.feedback.index')
                ->with('success', 'Thank you for your feedback.');
        }

        $data = $request->validate([
            'scope' => ['required', 'in:resort,room'],
            'accommodation_id' => ['nullable', 'required_if:scope,room', 'exists:accommodations,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Feedback::query()->create([
            'booking_id' => null,
            'guest_id' => $guest->id,
            'scope' => $data['scope'],
            'accommodation_id' => $data['scope'] === 'room' ? $data['accommodation_id'] : null,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('guest.feedback.index')
            ->with('success', 'Thank you for your feedback.');
    }
}
