<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'stripe_id',
        'stripe_customer_id',
        'invoice_number',
        'total_cents',
        'currency',
        'status',
        'billing_interval',
        'billing_interval_count',
        'customer_name',
        'customer_email',
        'due_date',
        'stripe_created_at',
    ];

    protected $casts = [
        'due_date'          => 'datetime',
        'stripe_created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
