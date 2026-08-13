<?php

namespace App\Providers;

use App\Domain\Auth\Contracts\UserRepository;
use App\Infrastructure\Repository\Auth\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
