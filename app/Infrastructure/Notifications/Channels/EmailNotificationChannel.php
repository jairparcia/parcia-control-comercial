<?php

namespace App\Infrastructure\Notifications\Channels;

use App\Domain\Notifications\Contracts\NotificationChannelInterface;
use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationChannel implements NotificationChannelInterface
{
    public function channel(): NotificationChannel
    {
        return NotificationChannel::Email;
    }

    public function send(CreateNotificationInputDTO $input): void
    {
        $user = User::find($input->userId);

        if (! $user) {
            return;
        }

        try {
            Mail::to($user->email)->send(new NotificationMail($input->title, $input->body));
        } catch (\Throwable $e) {
            Log::error('Notification email failed', [
                'user_id' => $input->userId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
