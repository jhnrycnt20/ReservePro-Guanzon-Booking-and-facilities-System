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
    ) {
    }

    public function create(): RedirectResponse|View
    {
        if (! request()->boolean('page')) {
            return redirect()
                ->route('front_desk.reservations.index')
                ->with('open_modal', 'walkin');
        }

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
                $walkInEmail = 'walkin-'.Str::lower(Str::random(16)).'@reservepro.local';

                $user = User::query()->firstOrCreate(
                    ['email' => $walkInEmail],
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

            foreach ($data['payments'] as $payment) {
                $amount = round((float) ($payment['amount'] ?? 0), 2);
                if ($amount <= 0) {
                    continue;
                }

                $this->paymentService->recordPayment($booking, [
                    'amount' => $amount,
                    'payment_method' => $payment['payment_method'] ?? 'cash',
                    'reference_number' => $payment['reference_number'] ?? null,
                    'payment_date' => now(),
                    'auto_verify' => true,
                    'notes' => 'Walk-in collection',
                ], $request->user());
            }

            return $booking->fresh();
        });

        $paymentCount = count($data['payments'] ?? []);
        $suffix = $paymentCount > 1 ? " ({$paymentCount} payments)" : '';

        return redirect()
            ->route('front_desk.reservations.index')
            ->with('open_view', $booking->id)
            ->with('success', 'Walk-in booking '.$booking->short_number.' created and fully paid'.$suffix.'.');
    }
}
