<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\AdminSubscriptionResult;

interface SubscriptionAdminRepositoryInterface
{
    /** @return AdminSubscriptionResult[] */
    public function all(string $statusFilter = 'active'): array;

    public function findByUserId(int $userId): ?AdminSubscriptionResult;

    /** @param ProviderSubscriptionDataDTO[] $subscriptions */
    public function insertMissing(array $subscriptions): int;

    public function markCanceled(string $stripeSubscriptionId): void;

    public function markScheduledCancellation(string $stripeSubscriptionId, \DateTimeImmutable $endsAt): void;
}
