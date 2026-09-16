<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\PromoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function __construct(protected PromoService $promoService)
    {
    }

    public function validateCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'promo_code' => ['required', 'string', 'max:32'],
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'nights' => ['nullable', 'integer', 'min:1'],
            'rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $promo = $this->promoService->findValidForAccommodation(
            $data['promo_code'],
            (int) $data['accommodation_id']
        );

        $rate = (float) ($data['rate'] ?? 0);
        $nights = max(1, (int) ($data['nights'] ?? 1));
        $original = round($rate * $nights, 2);
        $discount = $promo->discountAmount($original);
        $final = max(0, round($original - $discount, 2));

        return response()->json([
            'valid' => true,
            'code' => $promo->code,
            'discount_percent' => (float) $promo->discount_percent,
            'original_total' => $original,
            'discount_amount' => $discount,
            'promo_total' => $final,
            'promo_rate' => $promo->discountedRate($rate),
            'message' => "{$promo->discount_percent}% off applied with code {$promo->code}.",
        ]);
    }
}
