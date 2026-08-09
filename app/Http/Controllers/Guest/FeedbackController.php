<?php

namespace App\Http\Controllers\Guest;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
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
            ->with('booking.accommodation')
            ->latest()
            ->paginate(15);

        return view('guest.feedback.index', compact('feedback'));
    }

    public function create(Request $request, ?Booking $booking = null): View
    {
        $guestId = $request->user()->guest?->id;

        if ($booking) {
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

        return view('guest.feedback.create', compact('bookings'));
    }

    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $guest = $request->user()->guest;
        abort_unless($guest, 403);
        abort_unless($booking->guest_id === $guest->id, 403);
        abort_unless($booking->status === BookingStatus::CheckedOut, 422, 'Feedback allowed after check-out only.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Feedback::query()->updateOrCreate(
            ['booking_id' => $booking->id, 'guest_id' => $guest->id],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('guest.feedback.index')
            ->with('success', 'Thank you for your feedback.');
    }
}
