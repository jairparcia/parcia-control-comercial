<?php

namespace App\Domain\Notifications\Enums;

enum NotificationType: string
{
    case PlanPriceChanged = 'plan_price_changed';

    /** @return NotificationChannel[] */
    public function channels(): array
    {
        return match ($this) {
            self::PlanPriceChanged => [NotificationChannel::InApp, NotificationChannel::Email],
        };
    }
}
