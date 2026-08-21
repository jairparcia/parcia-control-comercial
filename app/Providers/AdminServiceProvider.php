<?php

namespace App\Providers;

use App\Domain\Admin\Contracts\PlanAdminRepository;
use App\Domain\Admin\Contracts\PlanProviderGateway;
use App\Infrastructure\Gateway\Stripe\StripePlanGateway;
use App\Infrastructure\Repository\Admin\EloquentPlanAdminRepository;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PlanAdminRepository::class, EloquentPlanAdminRepository::class);

        $this->app->bind(PlanProviderGateway::class, function () {
            return new StripePlanGateway(
                new StripeClient(config('cashier.secret')),
            );
        });
    }
}
