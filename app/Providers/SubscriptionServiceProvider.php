<?php

namespace App\Providers;

use App\Domain\Subscription\Contracts\PaymentGateway;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Infrastructure\Gateway\Stripe\StripePaymentGateway;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubscriptionRepository::class, CashierSubscriptionRepository::class);

        $this->app->bind(PaymentGateway::class, function () {
            return new StripePaymentGateway(
                new StripeClient(config('cashier.secret')),
            );
        });
    }
}
