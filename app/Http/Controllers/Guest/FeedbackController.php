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
    /** Public: browse all guest feedback without signing in. */
    public function publicIndex(Request $request): View
    {
        $feedback = Feedback::query()
            ->with(['guest.user', 'accommodation', 'booking.accommodation'])
            ->latest()
            ->paginate(12);

        $average = round((float) Feedback::query()->avg('rating'), 1);
        $count = Feedback::query()->count();

        return view('feedback.index', compact('feedback', 'average', 'count'));
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $guestId = $user?->guest?->id;
        abort_unless($guestId, 403);

        $baseQuery = Feedback::query()->where('guest_id', $guestId);

        $feedback = (clone $baseQuery)
            ->with(['booking.accommodation', 'accommodation'])
            ->latest()
            ->paginate(10);

        $average = round((float) (clone $baseQuery)->avg('rating'), 1);
        $count = (clone $baseQuery)->count();

        $ratingCounts = (clone $baseQuery)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $ratingBreakdown = [];
        for ($star = 5; $star >= 1; $star--) {
            $starCount = (int) ($ratingCounts[$star] ?? 0);
            $ratingBreakdown[$star] = [
                'count' => $starCount,
                'percent' => $count > 0 ? (int) round(($starCount / $count) * 100) : 0,
            ];
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

        return view('guest.feedback.index', compact(
            'feedback',
            'average',
            'count',
            'ratingBreakdown',
            'bookings',
            'accommodations',
            'user'
        ));
    }

    public function create(Request $request, ?Booking $booking = null): View|RedirectResponse
    {
        $guestId = $request->user()?->guest?->id;
        abort_unless($guestId, 403);

        if ($booking && $booking->exists) {
            abort_unless($booking->guest_id === $guestId, 403);
            abort_unless($booking->status === BookingStatus::CheckedOut, 422, 'Feedback allowed after check-out only.');
            $booking->load('accommodation');

            return view('guest.feedback.create', compact('booking'));
        }

        return redirect()->route('guest.feedback.index', ['write' => 1]);
    }

    public function store(Request $request, ?Booking $booking = null): RedirectResponse
    {
        $guest = $request->user()?->guest;
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
            'comment' => ['required', 'string', 'min:5', 'max:2000'],
        ], [
            'comment.required' => 'Please write your review.',
            'comment.min' => 'Please write a slightly longer review.',
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
