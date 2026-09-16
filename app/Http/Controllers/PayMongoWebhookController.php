<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\PayMongoService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __construct(
        protected PayMongoService $payMongo,
        protected PaymentService $paymentService,
    ) {
    }

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Paymongo-Signature');

        if (! $this->payMongo->verifyWebhookSignature($payload, $signature)) {
            Log::warning('PayMongo webhook rejected: invalid signature');

            return response('Invalid signature', 400);
        }

        $event = $this->payMongo->parseWebhookEvent($payload);
        if (! $event) {
            return response('Invalid payload', 400);
        }

        $type = $event['data']['attributes']['type'] ?? '';
        $payment = $this->payMongo->resolvePaymentFromWebhook($event);

        if (! $payment) {
            return response('OK', 200);
        }

        $verifier = $payment->processor ?? User::query()->whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();

        if (! $verifier) {
            Log::error('PayMongo webhook: no verifier user for payment '.$payment->id);

            return response('OK', 200);
        }

        if (in_array($type, ['payment.paid', 'payment_intent.succeeded'], true)) {
            $reference = $this->payMongo->extractGatewayReference($event);
            $this->paymentService->completeGatewayPayment($payment, $reference, $verifier);
        }

        if ($type === 'payment_intent.payment_failed' && $payment->status === PaymentStatus::Pending) {
            $payment->update([
                'status' => PaymentStatus::Rejected,
                'notes' => trim(($payment->notes ?? '').' PayMongo payment failed.'),
            ]);
        }

        return response('OK', 200);
    }
}
