<?php

namespace App\Domain\Auth\Contracts;

use App\Domain\Auth\Entities\GoogleCallbackInput;
use App\Models\User;

interface UserRepository
{
    public function findByGoogleId(string $googleId): ?User;

    public function findOrCreateByGoogle(GoogleCallbackInput $input): User;
}
