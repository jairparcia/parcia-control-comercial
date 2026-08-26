<?php

namespace App\Domain\Admin\Contracts;

use App\Domain\Admin\Entities\CancelSubscriptionInputDTO;
use App\Domain\Admin\Entities\CancelSubscriptionResultDTO;
use App\Domain\Admin\Entities\ProviderSubscriptionDataDTO;
use App\Domain\Admin\Results\SubscriptionCancellationInfoResult;

interface SubscriptionProviderGatewayInterface
{
    /** @return ProviderSubscriptionDataDTO[] */
    public function listAll(): array;

    /** @return ProviderSubscriptionDataDTO[] */
    public function listByCustomerId(string $stripeCustomerId): array;

    public function getCancellationInfo(string $stripeSubscriptionId): SubscriptionCancellationInfoResult;

    public function cancel(CancelSubscriptionInputDTO $input): CancelSubscriptionResultDTO;
}
