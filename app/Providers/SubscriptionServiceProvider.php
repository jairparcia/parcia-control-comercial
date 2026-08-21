<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Subscription\Contracts\PaymentGateway;
use App\Domain\Subscription\Contracts\SubscriptionPlanRepository;
use App\Domain\Subscription\Contracts\SubscriptionRepository;
use App\Infrastructure\Gateway\Stripe\StripePaymentGateway;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use App\Infrastructure\Repository\Subscription\EloquentSubscriptionPlanRepository;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubscriptionRepository::class, CashierSubscriptionRepository::class);

        $this->app->bind(SubscriptionPlanRepository::class, EloquentSubscriptionPlanRepository::class);

        $this->app->bind(PaymentGateway::class, function ($app) {
            return new StripePaymentGateway(
                $app->make(SubscriptionPlanRepository::class),
                $app->make(UserRepository::class),
            );
        });
    }
}
