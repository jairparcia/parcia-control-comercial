<?php

namespace App\Infrastructure\Repository\Auth;

use App\Domain\Auth\Contracts\UserRepositoryInterface;
use App\Domain\Auth\Entities\GoogleCallbackInputDTO;
use App\Models\User;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $userId): User
    {
        return User::findOrFail($userId);
    }

    public function findByGoogleId(string $googleId): ?User
    {
        return User::where('google_id', $googleId)->first();
    }

    public function findByStripeCustomerId(string $stripeCustomerId): ?User
    {
        return User::where('stripe_id', $stripeCustomerId)->first();
    }

    public function markOnboarded(int $userId): void
    {
        User::where('id', $userId)->update(['onboarded_at' => now()]);
    }

    public function findOrCreateByGoogle(GoogleCallbackInputDTO $input, string $role): User
    {
        $existing = User::where('google_id', $input->googleId)->first();

        if ($existing) {
            $existing->update([
                'name'   => $input->name,
                'email'  => $input->email,
                'avatar' => $input->avatar,
                'role'   => $role,
            ]);

            return $existing->fresh();
        }

        return User::create([
            'google_id' => $input->googleId,
            'name'      => $input->name,
            'email'     => $input->email,
            'avatar'    => $input->avatar,
            'role'      => $role,
        ]);
    }
}
