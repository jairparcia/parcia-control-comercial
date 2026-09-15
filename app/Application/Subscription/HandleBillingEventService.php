<?php

namespace App\Application\Subscription;

use App\Domain\Subscription\Entities\BillingEventInputDTO;
use App\Domain\Subscription\Enums\BillingEvent;
use App\Domain\Subscription\Enums\Plan;
use Illuminate\Support\Facades\Log;

class HandleBillingEventService
{
    public function execute(string $event, ?int $userId, ?string $planKey): void
    {
        $input = new BillingEventInputDTO(
            event:  BillingEvent::from($event),
            userId: $userId,
            plan:   $planKey ? Plan::from($planKey) : null,
        );

        match ($input->event) {
            BillingEvent::SubscriptionActivated => $this->onActivated($input),
            BillingEvent::SubscriptionCancelled => $this->onCancelled($input),
            BillingEvent::SubscriptionUpdated   => $this->onUpdated($input),
            BillingEvent::PaymentFailed         => $this->onPaymentFailed($input),
        };
    }

    private function onActivated(BillingEventInputDTO $input): void
    {
        // TODO: GenerateLicenseKeyService::execute($input->userId) — LicenseKeys context
        Log::info('Billing: subscription activated', [
            'user_id' => $input->userId,
            'plan'    => $input->plan?->value,
        ]);
    }

    private function onCancelled(BillingEventInputDTO $input): void
    {
        // TODO: RevokeLicenseKeyService::execute($input->userId) — LicenseKeys context
        Log::info('Billing: subscription cancelled', [
            'user_id' => $input->userId,
        ]);
    }

    private function onUpdated(BillingEventInputDTO $input): void
    {
        Log::info('Billing: subscription updated', [
            'user_id' => $input->userId,
            'plan'    => $input->plan?->value,
        ]);
    }

    private function onPaymentFailed(BillingEventInputDTO $input): void
    {
        // TODO: SendQuotaAlertJob (or dedicated payment failed email)
        Log::warning('Billing: payment failed', [
            'user_id' => $input->userId,
        ]);
    }
}
