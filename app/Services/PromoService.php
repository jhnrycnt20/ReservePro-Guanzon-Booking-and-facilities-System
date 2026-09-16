<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Promo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PromoService
{
    public function generateCode(int $length = 8): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (Promo::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    public function normalizeCode(?string $code): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $code) ?? '');
    }

    public function findValidForAccommodation(string $code, int $accommodationId): Promo
    {
        $normalized = $this->normalizeCode($code);

        if ($normalized === '') {
            throw ValidationException::withMessages([
                'promo_code' => 'Please enter a promo code.',
            ]);
        }

        $promo = Promo::query()
            ->with('accommodations:id')
            ->where('code', $normalized)
            ->first();

        if (! $promo || ! $promo->isCurrentlyValid()) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code is invalid or expired.',
            ]);
        }

        if (! $promo->appliesToAccommodation($accommodationId)) {
            throw ValidationException::withMessages([
                'promo_code' => 'This promo code does not apply to the selected accommodation.',
            ]);
        }

        return $promo;
    }

    public function previewPrices(Promo|array $promoOrPercent, ?Collection $accommodations = null): array
    {
        $percent = is_array($promoOrPercent)
            ? (float) ($promoOrPercent['discount_percent'] ?? 0)
            : (float) $promoOrPercent->discount_percent;

        $percent = max(0, min(100, $percent));
        $accommodations ??= Accommodation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'rate']);

        return $accommodations->map(function (Accommodation $item) use ($percent) {
            $original = (float) $item->rate;
            $discounted = round($original * (1 - ($percent / 100)), 2);

            return [
                'id' => $item->id,
                'name' => $item->name,
                'number' => $item->number,
                'original_rate' => $original,
                'promo_rate' => $discounted,
                'savings' => round($original - $discounted, 2),
            ];
        })->values()->all();
    }

    public function applyToTotals(array $totals, Promo $promo): array
    {
        $original = (float) $totals['total'];
        $discount = $promo->discountAmount($original);
        $final = max(0, round($original - $discount, 2));
        $promoRate = $promo->discountedRate((float) $totals['rate']);

        return array_merge($totals, [
            'original_total' => $original,
            'discount_amount' => $discount,
            'discount_percent' => (float) $promo->discount_percent,
            'rate' => $promoRate,
            'total' => $final,
            'promo' => $promo,
        ]);
    }

    public function markUsed(Promo $promo): void
    {
        $promo->increment('used_count');
    }
}
