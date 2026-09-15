<?php

namespace App\Infrastructure\Repository\Admin;

use App\Domain\Admin\Contracts\CustomerAdminRepositoryInterface;
use App\Domain\Admin\Results\AdminCustomerResult;
use App\Models\User;

class EloquentCustomerAdminRepository implements CustomerAdminRepositoryInterface
{
    public function all(string $statusFilter = 'all'): array
    {
        return User::query()
            ->withExists(['subscriptions as has_active_sub' => fn ($q) =>
                $q->whereIn('stripe_status', ['active', 'trialing'])
            ])
            ->when(in_array($statusFilter, ['active', 'inactive']), fn ($q) => $q->where('archived', false))
            ->when($statusFilter === 'archived', fn ($q) => $q->where('archived', true))
            ->when($statusFilter === 'active',    fn ($q) => $q->whereHas('subscriptions', fn ($q) =>
                $q->whereIn('stripe_status', ['active', 'trialing'])
            ))
            ->when($statusFilter === 'inactive',  fn ($q) => $q->whereDoesntHave('subscriptions', fn ($q) =>
                $q->whereIn('stripe_status', ['active', 'trialing'])
            ))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $user) => $this->toResult($user))
            ->all();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function update(int $id, ?string $description, ?string $country): void
    {
        User::where('id', $id)->update([
            'description' => $description,
            'country'     => $country,
        ]);
    }

    public function archive(int $id): void
    {
        User::where('id', $id)->update(['archived' => true]);
    }

    public function restore(int $id): void
    {
        User::where('id', $id)->update(['archived' => false]);
    }

    public function insertMissing(array $customers): int
    {
        $inserted = 0;

        foreach ($customers as $customer) {
            $user = User::where('stripe_id', $customer->providerCustomerId)->first()
                ?? User::where('email', $customer->email)->first();

            if ($user) {
                $updated = false;

                if (! $user->stripe_id) {
                    $user->stripe_id = $customer->providerCustomerId;
                    $updated = true;
                }

                if (! $user->description && $customer->description) {
                    $user->description = $customer->description;
                    $updated = true;
                }

                if (! $user->country && $customer->country) {
                    $user->country = $customer->country;
                    $updated = true;
                }

                if ($updated) {
                    $user->save();
                }

                continue;
            }

            User::create([
                'name'        => $customer->name ?? $customer->email,
                'email'       => $customer->email,
                'stripe_id'   => $customer->providerCustomerId,
                'description' => $customer->description,
                'country'     => $customer->country,
                'role'        => 'external',
            ]);

            $inserted++;
        }

        return $inserted;
    }

    private function toResult(User $user): AdminCustomerResult
    {
        return new AdminCustomerResult(
            id:           $user->id,
            name:         $user->name,
            email:        $user->email,
            description:  $user->description,
            country:      $user->country,
            archived:     (bool) $user->archived,
            hasActiveSub: (bool) ($user->has_active_sub ?? false),
            createdAt:    new \DateTimeImmutable($user->created_at->toDateTimeString()),
        );
    }
}
