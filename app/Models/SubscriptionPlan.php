<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'plans';

    protected $fillable = [
        'key',
        'name',
        'description',
        'features',
        'stripe_price_id',
        'legacy_stripe_price_ids',
        'stripe_product_id',
        'unit_amount',
        'currency',
        'interval',
        'quota',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active'                   => 'boolean',
            'features'                 => 'array',
            'legacy_stripe_price_ids'  => 'array',
            'unit_amount'              => 'integer',
            'quota'                    => 'integer',
            'sort_order'               => 'integer',
        ];
    }
}
