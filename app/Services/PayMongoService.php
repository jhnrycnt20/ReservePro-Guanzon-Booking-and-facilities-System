<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayMongoService
{
    protected const BASE_URL = 'https://api.paymongo.com/v1';

    public function createGcashSource(float $amountPesos, string $successUrl, string $failedUrl, array $billing = []): array
    {
        $response = $this->client()->post(self::BASE_URL.'/sources', [
            'data' => [
                'attributes' => [
                    'amount' => $this->toCentavos($amountPesos),
                    'currency' => 'PHP',
                    'type' => 'gcash',
                    'redirect' => [
                        'success' => $successUrl,
                        'failed' => $failedUrl,
                    ],
                    'billing' => $billing,
                ],
            ],
        ]);

        return $this->unwrap($response);
    }

    public function createPayment(string $sourceId, float $amountPesos, string $description): array
    {
        $response = $this->client()->post(self::BASE_URL.'/payments', [
            'data' => [
                'attributes' => [
                    'amount' => $this->toCentavos($amountPesos),
                    'currency' => 'PHP',
                    'description' => $description,
                    'source' => [
                        'id' => $sourceId,
                        'type' => 'source',
                    ],
                ],
            ],
        ]);

        return $this->unwrap($response);
    }

    public function retrieveSource(string $id): array
    {
        return $this->unwrap($this->client()->get(self::BASE_URL."/sources/{$id}"));
    }

    public function retrievePayment(string $id): array
    {
        return $this->unwrap($this->client()->get(self::BASE_URL."/payments/{$id}"));
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = (string) config('services.paymongo.webhook_secret');
        $header = (string) $request->header('Paymongo-Signature');

        if ($secret === '' || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $segment) {
            [$key, $value] = array_pad(explode('=', $segment, 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[trim($key)] = trim($value);
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['te'] ?? $parts['li'] ?? null;

        if (! $timestamp || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$request->getContent()}", $secret);

        return hash_equals($expected, $signature);
    }

    protected function client()
    {
        $secretKey = (string) config('services.paymongo.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('PayMongo secret key is not configured. Set PAYMONGO_SECRET_KEY in .env.');
        }

        return Http::withBasicAuth($secretKey, '')
            ->acceptJson()
            ->asJson();
    }

    protected function toCentavos(float $amountPesos): int
    {
        return (int) round($amountPesos * 100);
    }

    protected function unwrap($response): array
    {
        try {
            $response->throw();
        } catch (RequestException $exception) {
            Log::error('PayMongo API request failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw $exception;
        }

        return $response->json('data', []);
    }
}
