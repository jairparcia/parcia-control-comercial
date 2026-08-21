<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Subscription\Contracts\PaymentGatewayInterface;
use App\Domain\Subscription\Contracts\SubscriptionPlanRepositoryInterface;
use App\Domain\Subscription\Contracts\SubscriptionRepositoryInterface;
use App\Infrastructure\Gateway\Stripe\StripePaymentGateway;
use App\Infrastructure\Repository\Subscription\CashierSubscriptionRepository;
use App\Infrastructure\Repository\Subscription\EloquentSubscriptionPlanRepository;
use Illuminate\Support\ServiceProvider;

class SubscriptionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SubscriptionRepositoryInterface::class, CashierSubscriptionRepository::class);

        $this->app->bind(SubscriptionPlanRepositoryInterface::class, EloquentSubscriptionPlanRepository::class);

        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return new StripePaymentGateway(
                $app->make(SubscriptionPlanRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class),
            );
        });
    }
}
