<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Services\BookingService;
use App\Services\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function __construct(
        protected PromoService $promoService,
        protected BookingService $bookingService,
    )
    {
    }

    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'promo_code' => ['required', 'string', 'max:32'],
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'check_in_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date_format:Y-m-d', 'after:check_in_date'],
        ]);

        $accommodation = Accommodation::query()->findOrFail($data['accommodation_id']);
        $promo = $this->promoService->findValidForAccommodation(
            $data['promo_code'],
            (int) $accommodation->id
        );

        $totals = $this->bookingService->calculateTotal(
            $accommodation,
            $data['check_in_date'],
            $data['check_out_date']
        );
        $original = $totals['total'];
        $discount = $promo->discountAmount($original);
        $final = max(0, round($original - $discount, 2));

        return response()->json([
            'valid' => true,
            'code' => $promo->code,
            'discount_percent' => (float) $promo->discount_percent,
            'original_total' => $original,
            'discount_amount' => $discount,
            'promo_total' => $final,
            'promo_rate' => $promo->discountedRate($totals['rate']),
            'message' => "{$promo->discount_percent}% off applied with code {$promo->code}.",
        ]);
    }
}
