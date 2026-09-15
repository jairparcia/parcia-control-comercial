<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Billable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'avatar',
        'role',
        'description',
        'country',
        'archived',
        'webhook_url',
        'onboarded_at',
    ];

    protected $hidden = [];

    public $rememberTokenName = null;

    protected function casts(): array
    {
        return [
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'onboarded_at' => 'datetime',
            'archived'     => 'boolean',
        ];
    }

    public function isInternal(): bool
    {
        return $this->role === 'internal';
    }

    public function hasOnboarded(): bool
    {
        return $this->onboarded_at !== null;
    }

    public function initials(): string
    {
        return strtoupper(mb_substr($this->name, 0, 2));
    }
}
