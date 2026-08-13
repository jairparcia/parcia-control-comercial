<?php

namespace App\Infrastructure\Repository\Auth;

use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Auth\Entities\GoogleCallbackInput;
use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function findOrCreateByGoogle(GoogleCallbackInput $input): User
    {
        return User::updateOrCreate(
            ['google_id' => $input->googleId],
            [
                'name'   => $input->name,
                'email'  => $input->email,
                'avatar' => $input->avatar,
            ],
        );
    }
}
