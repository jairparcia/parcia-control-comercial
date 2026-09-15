<?php

namespace App\Domain\Auth\Contracts;

use App\Domain\Auth\Entities\GoogleCallbackInputDTO;
use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $userId): User;

    public function findByGoogleId(string $googleId): ?User;

    public function findByStripeCustomerId(string $stripeCustomerId): ?User;

    public function findOrCreateByGoogle(GoogleCallbackInputDTO $input, string $role): User;

    public function markOnboarded(int $userId): void;
}
