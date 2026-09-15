<?php

namespace App\Providers;

use App\Application\Notifications\NotificationService;
use App\Domain\Notifications\Contracts\NotificationRepositoryInterface;
use App\Infrastructure\Notifications\Channels\EmailNotificationChannel;
use App\Infrastructure\Notifications\Channels\InAppNotificationChannel;
use App\Infrastructure\Repository\Notifications\EloquentNotificationRepository;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NotificationRepositoryInterface::class,
            EloquentNotificationRepository::class,
        );

        $this->app->bind(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationRepositoryInterface::class),
                [
                    $app->make(InAppNotificationChannel::class),
                    $app->make(EmailNotificationChannel::class),
                ],
            );
        });
    }
}
