<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        return [
            'user_id'               => null,
            'stripe_id'             => 'ch_' . $this->faker->unique()->regexify('[A-Za-z0-9]{24}'),
            'stripe_customer_id'    => 'cus_' . $this->faker->regexify('[A-Za-z0-9]{14}'),
            'amount_cents'          => $this->faker->numberBetween(10000, 500000),
            'amount_refunded_cents' => 0,
            'currency'              => 'MXN',
            'status'                => 'succeeded',
            'payment_method_type'   => 'card',
            'card_brand'            => 'Visa',
            'card_last4'            => $this->faker->numerify('####'),
            'description'           => 'Subscription payment',
            'customer_name'         => $this->faker->name(),
            'customer_email'        => $this->faker->safeEmail(),
            'stripe_created_at'     => now()->subDays($this->faker->numberBetween(1, 60)),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(['status' => 'succeeded', 'amount_refunded_cents' => 0]);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed']);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'                => 'refunded',
            'amount_refunded_cents' => $attrs['amount_cents'],
        ]);
    }

    public function partiallyRefunded(): static
    {
        return $this->state(fn (array $attrs) => [
            'status'                => 'partially_refunded',
            'amount_refunded_cents' => (int) ($attrs['amount_cents'] / 2),
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state([
            'user_id'            => $user->id,
            'stripe_customer_id' => $user->stripe_id,
        ]);
    }
}
