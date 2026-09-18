<?php

use App\Domain\Notifications\Enums\NotificationType;
use App\Events\PlanPriceChanged;
use App\Mail\NotificationMail;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

it('creates an in-app notification and emails every active subscriber when a plan price changes', function () {
    Mail::fake();

    $plan = SubscriptionPlan::create([
        'key'               => 'pro',
        'name'              => 'Pro',
        'unit_amount'       => 80000,
        'currency'          => 'USD',
        'interval'          => 'year',
        'quota'             => 1000,
        'sort_order'        => 1,
        'active'            => true,
        'stripe_product_id' => 'prod_TEST',
    ]);

    $subscriber   = User::factory()->create();
    $unrelatedUser = User::factory()->create();

    $sub = Subscription::create([
        'user_id'       => $subscriber->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_TEST',
        'stripe_status' => 'active',
        'stripe_price'  => 'price_TEST',
        'quantity'      => 1,
    ]);

    DB::table('subscription_items')->insert([
        'subscription_id' => $sub->id,
        'stripe_id'       => 'si_TEST',
        'stripe_product'  => 'prod_TEST',
        'stripe_price'    => 'price_TEST',
        'quantity'        => 1,
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    event(new PlanPriceChanged(
        planId:             $plan->id,
        planName:           'Pro',
        newUnitAmountCents: 80000,
        currency:           'USD',
        interval:           'year',
    ));

    expect(Notification::where('user_id', $subscriber->id)->count())->toBe(1)
        ->and(Notification::where('user_id', $unrelatedUser->id)->count())->toBe(0);

    $notification = Notification::where('user_id', $subscriber->id)->first();

    expect($notification->type)->toBe(NotificationType::PlanPriceChanged->value)
        ->and($notification->title)->toBe(__('notifications.plan_price_changed_title', ['plan' => 'Pro']))
        ->and($notification->read_at)->toBeNull();

    Mail::assertQueued(NotificationMail::class, function (NotificationMail $mail) use ($subscriber, $notification) {
        return $mail->hasTo($subscriber->email)
            && $mail->title === $notification->title;
    });
    Mail::assertQueued(NotificationMail::class, 1);
});

it('does not create any notification or email when the plan has no active subscribers', function () {
    Mail::fake();

    $plan = SubscriptionPlan::create([
        'key'               => 'starter',
        'name'              => 'Starter',
        'unit_amount'       => 30000,
        'currency'          => 'MXN',
        'interval'          => 'month',
        'quota'             => 500,
        'sort_order'        => 1,
        'active'            => true,
        'stripe_product_id' => 'prod_EMPTY',
    ]);

    event(new PlanPriceChanged(
        planId:             $plan->id,
        planName:           'Starter',
        newUnitAmountCents: 30000,
        currency:           'MXN',
        interval:           'month',
    ));

    expect(Notification::count())->toBe(0);
    Mail::assertNothingQueued();
    Mail::assertNothingSent();
});
