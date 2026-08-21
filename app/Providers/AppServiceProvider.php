<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Infrastructure\Repository\Auth\EloquentUserRepository;
use App\Models\Subscription;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        Cashier::useSubscriptionModel(Subscription::class);
    }
}
