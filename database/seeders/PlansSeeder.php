<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    // Only structural plans that never go through Stripe checkout.
    // Paid plans (starter, pro, agency, …) are created via the admin panel.
    private array $plans = [
        'free' => [
            'name'        => 'Gratuito',
            'unit_amount' => 0,
            'currency'    => 'MXN',
            'interval'    => 'month',
            'quota'       => 10,
            'sort_order'  => 0,
            'active'      => true,
        ],
        'internal' => [
            'name'        => 'Interno',
            'unit_amount' => 0,
            'currency'    => 'MXN',
            'interval'    => 'month',
            'quota'       => 999999999,
            'sort_order'  => 99,
            'active'      => true,
        ],
    ];

    public function run(): void
    {
        foreach ($this->plans as $key => $data) {
            SubscriptionPlan::updateOrCreate(['key' => $key], $data);
        }
    }
}
