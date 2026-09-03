<?php

namespace App\Infrastructure\Gateway\Stripe;

use App\Domain\Admin\Contracts\TransactionProviderGatewayInterface;
use App\Domain\Admin\Entities\ProviderTransactionDataDTO;
use App\Domain\Admin\Results\TransactionDetailResult;
use App\Domain\Admin\Results\TransactionEventResult;
use App\Domain\Admin\Results\TransactionFeeDetailResult;
use Stripe\StripeClient;

class StripeTransactionGateway implements TransactionProviderGatewayInterface
{
    public function __construct(
        private readonly StripeClient $client,
    ) {}

    public function listAll(int $limit = 100): array
    {
        $results = [];
        $page    = $this->client->charges->all(['limit' => min($limit, 100)]);

        foreach ($page->data as $charge) {
            $methodDetails = $charge->payment_method_details;

            $results[] = new ProviderTransactionDataDTO(
                stripeId:            $charge->id,
                amountCents:         (int) $charge->amount,
                amountRefundedCents: (int) ($charge->amount_refunded ?? 0),
                currency:            strtoupper($charge->currency),
                status:              $this->resolveStatus($charge),
                paymentMethodType:   $methodDetails?->type ?? null,
                cardBrand:           $methodDetails?->card?->brand ? ucfirst($methodDetails->card->brand) : null,
                cardLast4:           $methodDetails?->card?->last4 ?? null,
                description:         $charge->description ?: null,
                customerName:        $charge->billing_details?->name ?: null,
                customerEmail:       $charge->billing_details?->email ?: null,
                stripeCustomerId:    is_string($charge->customer) ? $charge->customer : null,
                createdAt:           new \DateTimeImmutable('@' . $charge->created),
            );
        }

        return $results;
    }

    public function getTransactionDetail(string $chargeId): ?TransactionDetailResult
    {
        try {
            $charge = $this->client->charges->retrieve($chargeId, [
                'expand' => [
                    'balance_transaction',
                    'invoice',
                ],
            ]);

            $balanceTx       = is_object($charge->balance_transaction) ? $charge->balance_transaction : null;
            $stripeFeesCents = (int) ($balanceTx?->fee ?? 0);
            $netAmountCents  = (int) ($balanceTx?->net ?? 0);
            $feeDetails      = $this->extractFeeDetails($balanceTx, strtoupper($charge->currency));

            $pm   = $charge->payment_method_details;
            $card = $pm?->card ?? null;

            $invoice = is_object($charge->invoice) ? $charge->invoice : null;

            logger()->debug('[StripeTransactionGateway] charge detail', [
                'chargeId'          => $chargeId,
                'has_invoice'       => ! is_null($invoice),
                'invoice_raw_type'  => gettype($charge->invoice),
                'invoice_id'        => $invoice?->id,
                'subscription_raw'  => $invoice?->subscription,
            ]);

            // Subscription ID — invoice.subscription is a string (ID) when not expanded
            $subscriptionRaw = $invoice?->subscription ?? null;
            $subscriptionId  = is_string($subscriptionRaw) && $subscriptionRaw
                ? $subscriptionRaw
                : (is_object($subscriptionRaw) ? ($subscriptionRaw->id ?? null) : null);

            // Retrieve the subscription directly to get plan details reliably.
            // invoice.lines may not always carry the price object when the invoice
            // is expanded from a charge rather than retrieved standalone.
            $planName = null;
            $priceId  = null;

            if ($subscriptionId) {
                try {
                    $sub       = $this->client->subscriptions->retrieve($subscriptionId, [
                        'expand' => ['items.data.price'],
                    ]);
                    $firstItem = $sub->items->data[0] ?? null;
                    $planName  = $firstItem?->price?->nickname ?? null;
                    $priceId   = $firstItem?->price?->id ?? null;
                } catch (\Throwable) {
                    // fall through to invoice line items below
                }
            }

            // Fallback: try invoice line items (works for both Plans and Prices API)
            if (! $planName) {
                $firstLine = $invoice?->lines?->data[0] ?? null;
                $planName  = $firstLine?->plan?->nickname ?? $firstLine?->price?->nickname ?? null;
                $priceId   = $priceId ?? $firstLine?->plan?->id ?? $firstLine?->price?->id ?? null;
            }

            $expMonth = $card?->exp_month
                ? str_pad((string) $card->exp_month, 2, '0', STR_PAD_LEFT)
                : null;
            $expYear  = $card?->exp_year ? (string) $card->exp_year : null;

            return new TransactionDetailResult(
                stripeId:        $charge->id,
                amountCents:     (int) $charge->amount,
                currency:        strtoupper($charge->currency),
                status:          $this->resolveStatus($charge),
                customerName:    $charge->billing_details?->name ?: null,
                customerEmail:   $charge->billing_details?->email ?: null,
                stripeFeesCents: $stripeFeesCents,
                netAmountCents:  $netAmountCents,
                paymentMethodId: is_string($charge->payment_method) ? $charge->payment_method : null,
                cardLast4:       $card?->last4 ?? null,
                cardFingerprint: $card?->fingerprint ?? null,
                cardExpMonth:    $expMonth,
                cardExpYear:     $expYear,
                cardFunding:     $card?->funding ?? null,
                cardBrand:       $card?->brand ? ucfirst($card->brand) : null,
                cardIssuer:      $card?->issuer ?? null,
                cardCountry:     $card?->country ?? null,
                cvcCheck:        $card?->checks?->cvc_check ?? null,
                billingName:     $charge->billing_details?->name ?: null,
                billingEmail:    $charge->billing_details?->email ?: null,
                billingCountry:  $charge->billing_details?->address?->country ?: null,
                subscriptionId:  $subscriptionId,
                planName:        $planName,
                priceId:         $priceId,
                invoiceNumber:   $invoice?->number ?? null,
                paymentIntentId: is_string($charge->payment_intent) ? $charge->payment_intent : null,
                events:          $this->buildTimeline($charge),
                createdAt:       new \DateTimeImmutable('@' . $charge->created),
                feeDetails:      $feeDetails,
            );
        } catch (\Throwable $e) {
            report($e);
            return null;
        }
    }

    private function extractFeeDetails(?object $balanceTx, string $currency): array
    {
        if (! $balanceTx || empty($balanceTx->fee_details)) {
            return [];
        }

        $results = [];
        foreach ($balanceTx->fee_details as $detail) {
            $results[] = new TransactionFeeDetailResult(
                type:        $detail->type ?? 'stripe_fee',
                description: $detail->description ?? ucfirst(str_replace('_', ' ', $detail->type ?? '')),
                amountCents: (int) ($detail->amount ?? 0),
                currency:    strtoupper($detail->currency ?? $currency),
            );
        }

        return $results;
    }

    private function buildTimeline(object $charge): array
    {
        $ts     = $charge->created;
        $events = [];

        $events[] = new TransactionEventResult('Pago iniciado', new \DateTimeImmutable('@' . $ts));

        if ($charge->payment_method) {
            $events[] = new TransactionEventResult(
                'Método de pago configurado',
                new \DateTimeImmutable('@' . $ts),
            );
        }

        if ($charge->status === 'succeeded') {
            $events[] = new TransactionEventResult(
                'Pago efectuado correctamente',
                new \DateTimeImmutable('@' . $ts),
            );
        } elseif ($charge->status === 'failed') {
            $events[] = new TransactionEventResult(
                'Pago fallido',
                new \DateTimeImmutable('@' . $ts),
            );
        }

        if ($charge->refunded) {
            $refund = $charge->refunds?->data[0] ?? null;
            $refundTs = $refund ? $refund->created : $ts;
            $events[] = new TransactionEventResult(
                'Pago reembolsado',
                new \DateTimeImmutable('@' . $refundTs),
            );
        }

        return array_reverse($events);
    }

    private function resolveStatus(object $charge): string
    {
        if ($charge->refunded) {
            return 'refunded';
        }

        if (($charge->amount_refunded ?? 0) > 0) {
            return 'partially_refunded';
        }

        return $charge->status;
    }
}
