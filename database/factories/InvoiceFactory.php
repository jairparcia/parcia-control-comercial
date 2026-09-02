<?php

namespace Database\Factories;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'user_id'                => null,
            'stripe_id'              => 'in_' . fake()->unique()->regexify('[A-Za-z0-9]{24}'),
            'stripe_customer_id'     => 'cus_' . fake()->regexify('[A-Za-z0-9]{14}'),
            'invoice_number'         => strtoupper(fake()->regexify('[A-Z0-9]{8}-[0-9]{4}')),
            'total_cents'            => fake()->numberBetween(1000, 999900),
            'currency'               => 'MXN',
            'status'                 => 'paid',
            'billing_interval'       => 'month',
            'billing_interval_count' => 1,
            'customer_name'          => fake()->name(),
            'customer_email'         => fake()->safeEmail(),
            'due_date'               => null,
            'stripe_created_at'      => now()->subDays(fake()->numberBetween(1, 90)),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid']);
    }

    public function open(): static
    {
        return $this->state(fn () => ['status' => 'open']);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }

    public function void(): static
    {
        return $this->state(fn () => ['status' => 'void']);
    }

    public function forUser(\App\Models\User $user): static
    {
        return $this->state(fn () => [
            'user_id'            => $user->id,
            'stripe_customer_id' => $user->stripe_id,
            'customer_name'      => $user->name,
            'customer_email'     => $user->email,
        ]);
    }
}
