<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'stripe_customer_id',
        'amount_cents',
        'amount_refunded_cents',
        'currency',
        'status',
        'payment_method_type',
        'card_brand',
        'card_last4',
        'description',
        'customer_name',
        'customer_email',
        'stripe_created_at',
    ];

    protected $casts = [
        'stripe_created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
