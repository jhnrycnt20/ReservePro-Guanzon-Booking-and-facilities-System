<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PayMongoService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongoService,
        protected PaymentService $paymentService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        if (! $this->payMongoService->verifyWebhookSignature($request)) {
            Log::warning('PayMongo webhook signature verification failed.');

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $type = $request->input('data.attributes.type');
        $eventData = $request->input('data.attributes.data', []);

        match ($type) {
            'source.chargeable' => $this->handleSourceChargeable($eventData),
            'payment.paid' => $this->handlePaymentPaid($eventData),
            'payment.failed' => $this->handlePaymentFailed($eventData),
            default => Log::info('Unhandled PayMongo webhook event.', ['type' => $type]),
        };

        return response()->json(['message' => 'ok']);
    }

    protected function handleSourceChargeable(array $source): void
    {
        $sourceId = $source['id'] ?? null;
        if (! $sourceId) {
            return;
        }

        $payment = Payment::query()->where('gateway_source_id', $sourceId)->first();
        if (! $payment || $payment->status !== PaymentStatus::Pending || $payment->gateway_payment_id) {
            return;
        }

        $this->payMongoService->createPayment(
            $sourceId,
            (float) $payment->amount,
            "Booking #{$payment->booking->booking_number} GCash payment"
        );
    }

    protected function handlePaymentPaid(array $paymentData): void
    {
        $payment = $this->findPaymentForGatewayPayment($paymentData);
        if (! $payment) {
            Log::warning('PayMongo payment.paid webhook for unknown payment.', ['data' => $paymentData]);

            return;
        }

        $this->paymentService->finalizeGatewayPayment(
            $payment,
            (string) ($paymentData['id'] ?? ''),
            (string) ($paymentData['attributes']['status'] ?? 'paid')
        );
    }

    protected function handlePaymentFailed(array $paymentData): void
    {
        $payment = $this->findPaymentForGatewayPayment($paymentData);
        if (! $payment) {
            Log::warning('PayMongo payment.failed webhook for unknown payment.', ['data' => $paymentData]);

            return;
        }

        $reason = $paymentData['attributes']['failed_payment']['failure_message']
            ?? 'GCash payment failed or was declined.';

        $this->paymentService->failGatewayPayment($payment, (string) $reason);
    }

    protected function findPaymentForGatewayPayment(array $paymentData): ?Payment
    {
        $paymentId = $paymentData['id'] ?? null;
        $sourceId = $paymentData['attributes']['source']['id'] ?? null;

        return Payment::query()
            ->when($paymentId, fn ($query) => $query->orWhere('gateway_payment_id', $paymentId))
            ->when($sourceId, fn ($query) => $query->orWhere('gateway_source_id', $sourceId))
            ->first();
    }
}
