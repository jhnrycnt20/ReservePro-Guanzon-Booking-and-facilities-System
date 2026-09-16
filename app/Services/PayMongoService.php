<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PayMongoService
{
    public const GATEWAY = 'paymongo';

    public function isConfigured(): bool
    {
        if (! config('paymongo.enabled')) {
            return false;
        }

        $secret = config('paymongo.secret_key');

        return is_string($secret) && $secret !== '';
    }

    /**
     * @return array{checkout_url: string, payment_intent_id: string}
     */
    public function createGcashCheckout(Payment $payment, Booking $booking, User $payer): array
    {
        if (! $this->isConfigured()) {
            throw ValidationException::withMessages([
                'payment' => 'Online GCash payments are not configured.',
            ]);
        }

        $amountCentavos = (int) round(((float) $payment->amount) * 100);
        if ($amountCentavos < 100) {
            throw ValidationException::withMessages([
                'amount' => 'Minimum online payment is ₱1.00.',
            ]);
        }

        $returnUrl = route('guest.payments.gcash.return', [
            'payment' => $payment->id,
        ]);

        $billing = [
            'name' => $payer->name ?: 'Guest',
            'email' => $payer->email ?: 'guest@reservepro.test',
            'phone' => preg_replace('/\D/', '', $payer->phone ?? '') ?: '09000000000',
        ];

        $intentId = $this->createPaymentIntent($amountCentavos, $payment->id, $booking->id);
        $paymentMethodId = $this->createGcashPaymentMethod($billing);
        $attach = $this->attachPaymentMethod($intentId, $paymentMethodId, $returnUrl);

        $checkoutUrl = $attach['data']['attributes']['redirect']['checkout_url']
            ?? $attach['data']['attributes']['next_action']['redirect']['url']
            ?? null;

        if (! $checkoutUrl) {
            throw ValidationException::withMessages([
                'payment' => 'PayMongo did not return a GCash checkout URL.',
            ]);
        }

        return [
            'checkout_url' => $checkoutUrl,
            'payment_intent_id' => $intentId,
        ];
    }

    public function verifyWebhookSignature(string $payload, ?string $signatureHeader): bool
    {
        $secret = config('paymongo.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        if (! $signatureHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['te'] ?? $parts['li'] ?? null;

        if (! $timestamp || ! $signature) {
            return false;
        }

        $signedPayload = $timestamp.'.'.$payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parseWebhookEvent(string $payload): ?array
    {
        $decoded = json_decode($payload, true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Resolve local payment id from webhook payload (metadata or payment intent id).
     */
    public function resolvePaymentFromWebhook(array $event): ?Payment
    {
        $attributes = $event['data']['attributes'] ?? [];
        $type = $attributes['type'] ?? '';
        $inner = $attributes['data']['attributes'] ?? $attributes['data'] ?? [];

        if ($type === 'payment_intent.succeeded' || $type === 'payment.paid') {
            $resource = $attributes['data'] ?? [];
            $resourceAttrs = $resource['attributes'] ?? [];
            $intentId = $resourceAttrs['payment_intent_id'] ?? null;

            if (! $intentId && is_string($resource['id'] ?? null) && str_starts_with($resource['id'], 'pi_')) {
                $intentId = $resource['id'];
            }

            if (is_string($intentId) && str_starts_with($intentId, 'pi_')) {
                return Payment::query()->where('gateway_ref', $intentId)->first();
            }

            $metadata = $resourceAttrs['metadata'] ?? $inner['metadata'] ?? [];
            if (! empty($metadata['payment_id'])) {
                return Payment::query()->find($metadata['payment_id']);
            }
        }

        if ($type === 'payment_intent.payment_failed') {
            $intentId = $attributes['data']['id'] ?? null;
            if (is_string($intentId)) {
                return Payment::query()->where('gateway_ref', $intentId)->first();
            }
        }

        return null;
    }

    public function extractGatewayReference(array $event): ?string
    {
        $attributes = $event['data']['attributes'] ?? [];
        $type = $attributes['type'] ?? '';
        $data = $attributes['data'] ?? [];

        if ($type === 'payment.paid') {
            return $data['id'] ?? null;
        }

        if ($type === 'payment_intent.succeeded') {
            return $data['id'] ?? null;
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function apiPost(string $path, array $body): array
    {
        $secret = config('paymongo.secret_key');

        $response = Http::withBasicAuth($secret, '')
            ->acceptJson()
            ->asJson()
            ->post('https://api.paymongo.com/v1'.$path, $body);

        if ($response->failed()) {
            $message = $response->json('errors.0.detail')
                ?? $response->json('errors.0.code')
                ?? 'PayMongo request failed.';

            throw ValidationException::withMessages([
                'payment' => $message,
            ]);
        }

        return $response->json();
    }

    protected function createPaymentIntent(int $amountCentavos, int $paymentId, int $bookingId): string
    {
        $response = $this->apiPost('/payment_intents', [
            'data' => [
                'attributes' => [
                    'amount' => $amountCentavos,
                    'currency' => 'PHP',
                    'payment_method_allowed' => ['gcash'],
                    'capture_type' => 'automatic',
                    'metadata' => [
                        'payment_id' => (string) $paymentId,
                        'booking_id' => (string) $bookingId,
                    ],
                ],
            ],
        ]);

        $id = $response['data']['id'] ?? null;
        if (! is_string($id)) {
            throw ValidationException::withMessages([
                'payment' => 'Unable to start PayMongo payment.',
            ]);
        }

        return $id;
    }

    protected function createGcashPaymentMethod(array $billing): string
    {
        $response = $this->apiPost('/payment_methods', [
            'data' => [
                'attributes' => [
                    'type' => 'gcash',
                    'billing' => $billing,
                ],
            ],
        ]);

        $id = $response['data']['id'] ?? null;
        if (! is_string($id)) {
            throw ValidationException::withMessages([
                'payment' => 'Unable to prepare GCash payment method.',
            ]);
        }

        return $id;
    }

    protected function attachPaymentMethod(string $intentId, string $paymentMethodId, string $returnUrl): array
    {
        return $this->apiPost("/payment_intents/{$intentId}/attach", [
            'data' => [
                'attributes' => [
                    'payment_method' => $paymentMethodId,
                    'return_url' => $returnUrl,
                ],
            ],
        ]);
    }
}
