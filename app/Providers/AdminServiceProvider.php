<?php

namespace App\Providers;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Contracts\CustomerProviderGatewayInterface;
use App\Domain\Admin\Contracts\InvoiceAdminRepositoryInterface;
use App\Domain\Admin\Contracts\InvoiceProviderGatewayInterface;
use App\Domain\Admin\Contracts\PlanAdminRepositoryInterface;
use App\Domain\Admin\Contracts\PlanProviderGatewayInterface;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Contracts\SubscriptionProviderGatewayInterface;
use App\Infrastructure\Gateway\Stripe\StripeCustomerGateway;
use App\Infrastructure\Gateway\Stripe\StripeInvoiceGateway;
use App\Infrastructure\Gateway\Stripe\StripePlanGateway;
use App\Infrastructure\Gateway\Stripe\StripeSubscriptionGateway;
use App\Infrastructure\Repository\Admin\EloquentCustomerAdminRepository;
use App\Infrastructure\Repository\Admin\EloquentInvoiceAdminRepository;
use App\Infrastructure\Repository\Admin\EloquentPlanAdminRepository;
use App\Infrastructure\Repository\Admin\EloquentSubscriptionAdminRepository;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlanAdminRepositoryInterface::class, EloquentPlanAdminRepository::class);

        $this->app->bind(InvoiceAdminRepositoryInterface::class, EloquentInvoiceAdminRepository::class);

        $this->app->bind(SubscriptionAdminRepositoryInterface::class, EloquentSubscriptionAdminRepository::class);

        $this->app->bind(CustomerAdminRepositoryInterface::class, EloquentCustomerAdminRepository::class);

        $this->app->bind(PlanProviderGatewayInterface::class, function () {
            return new StripePlanGateway(
                new StripeClient(config('cashier.secret')),
            );
        });

        $this->app->bind(SubscriptionProviderGatewayInterface::class, function () {
            return new StripeSubscriptionGateway(
                new StripeClient(config('cashier.secret')),
            );
        });

        $this->app->bind(CustomerProviderGatewayInterface::class, function () {
            return new StripeCustomerGateway(
                new StripeClient(config('cashier.secret')),
            );
        });

        $this->app->bind(InvoiceProviderGatewayInterface::class, function () {
            return new StripeInvoiceGateway(
                new StripeClient(config('cashier.secret')),
            );
        });
    }
}
