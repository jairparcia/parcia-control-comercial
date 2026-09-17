<?php

use App\Domain\Notifications\Entities\CreateNotificationInputDTO;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Enums\NotificationType;
use App\Infrastructure\Notifications\Channels\EmailNotificationChannel;
use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('identifies itself as the email channel', function () {
    expect((new EmailNotificationChannel())->channel())->toBe(NotificationChannel::Email);
});

it('send() emails the user with the notification title and body', function () {
    Mail::fake();

    $user = User::factory()->create(['email' => 'subscriber@example.com']);

    $input = new CreateNotificationInputDTO($user->id, NotificationType::PlanPriceChanged, 'Price update: Pro', 'The Pro plan changed.');

    (new EmailNotificationChannel())->send($input);

    Mail::assertQueued(NotificationMail::class, function (NotificationMail $mail) use ($user) {
        return $mail->hasTo($user->email)
            && $mail->title === 'Price update: Pro'
            && $mail->body === 'The Pro plan changed.';
    });
});

it('send() does nothing when the user does not exist', function () {
    Mail::fake();

    $input = new CreateNotificationInputDTO(999999, NotificationType::PlanPriceChanged, 'Title', 'Body');

    (new EmailNotificationChannel())->send($input);

    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});

