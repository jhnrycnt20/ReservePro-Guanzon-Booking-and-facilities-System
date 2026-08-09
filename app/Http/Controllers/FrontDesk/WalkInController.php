<?php

namespace App\Http\Controllers\FrontDesk;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\FrontDesk\StoreWalkInRequest;
use App\Models\Accommodation;
use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use App\Services\BookingService;
use App\Services\CheckInService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WalkInController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected PaymentService $paymentService,
        protected CheckInService $checkInService,
    ) {
    }

    public function create(): View
    {
        $accommodations = Accommodation::query()
            ->where('is_active', true)
            ->whereNotIn('status', ['maintenance', 'inactive'])
            ->orderBy('name')
            ->get();

        return view('front_desk.walkins.create', compact('accommodations'));
    }

    public function store(StoreWalkInRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $booking = DB::transaction(function () use ($data, $request) {
            if (! empty($data['guest_id'])) {
                $guest = Guest::query()->findOrFail($data['guest_id']);
            } else {
                $guestRole = Role::query()->where('slug', UserRole::Guest->value)->firstOrFail();

                $user = User::query()->firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'role_id' => $guestRole->id,
                        'name' => $data['guest_name'],
                        'phone' => $data['contact_number'],
                        'password' => Hash::make(Str::password(12)),
                        'is_active' => true,
                    ]
                );

                $guest = Guest::query()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['contact_number' => $data['contact_number']]
                );
            }

            $booking = $this->bookingService->createReservation(
                $data,
                $guest,
                $request->user(),
                true
            );

            if ($request->boolean('auto_approve', true)) {
                $booking = $this->bookingService->approve($booking, $request->user());
            }

            if (! empty($data['payment_amount']) && (float) $data['payment_amount'] > 0) {
                $this->paymentService->recordPayment($booking, [
                    'amount' => $data['payment_amount'],
                    'payment_method' => $data['payment_method'] ?? 'cash',
                    'reference_number' => $data['reference_number'] ?? null,
                    'payment_date' => now(),
                    'auto_verify' => true,
                ], $request->user());
            }

            if ($request->boolean('auto_check_in')) {
                $booking = $booking->fresh();
                if ((float) $booking->remaining_balance <= 0 && $booking->status->value === 'approved') {
                    $this->checkInService->checkIn($booking, $request->user(), 'Walk-in check-in');
                }
            }

            return $booking->fresh();
        });

        return redirect()
            ->route('front_desk.reservations.show', $booking)
            ->with('success', 'Walk-in booking processed successfully.');
    }
}
