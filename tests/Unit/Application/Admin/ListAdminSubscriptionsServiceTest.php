<?php

use App\Application\Admin\ListAdminSubscriptionsService;
use App\Domain\Admin\Contracts\SubscriptionAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminSubscriptionResult;
// ── Helpers ───────────────────────────────────────────────────────────────────

function makeListSubResult(array $overrides = []): AdminSubscriptionResult
{
    return new AdminSubscriptionResult(
        id:          $overrides['id']        ?? 1,
        stripeId:    $overrides['stripeId']  ?? 'sub_TEST',
        status:      $overrides['status']    ?? 'active',
        userName:    $overrides['userName']  ?? 'Jane Doe',
        userEmail:   $overrides['userEmail'] ?? 'jane@example.com',
        pmType:      null,
        pmLastFour:  null,
        planName:    'Starter',
        planKey:     'starter',
        unitAmount:  50000,
        currency:    'MXN',
        interval:    'month',
        subscribedAt: new \DateTimeImmutable('2025-01-15'),
        endsAt:      null,
    );
}

// ── Tests ─────────────────────────────────────────────────────────────────────

it('returns all subscriptions from the repository', function () {
    $results = [makeListSubResult(['id' => 1]), makeListSubResult(['id' => 2])];

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('all')->once()->andReturn($results);

    expect((new ListAdminSubscriptionsService($repo))->execute())->toBe($results);
});

it('returns an empty array when there are no subscriptions', function () {
    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->expects('all')->once()->andReturn([]);

    expect((new ListAdminSubscriptionsService($repo))->execute())->toBeEmpty();
});

it('delegates entirely to the repository with no transformation', function () {
    $result = makeListSubResult(['userName' => 'Specific Name']);

    $repo = Mockery::mock(SubscriptionAdminRepositoryInterface::class);
    $repo->allows('all')->andReturn([$result]);

    $output = (new ListAdminSubscriptionsService($repo))->execute();

    expect($output[0]->userName)->toBe('Specific Name');
});
