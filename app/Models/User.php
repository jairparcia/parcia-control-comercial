<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'avatar',
        'role',
        'webhook_url',
    ];

    protected $hidden = [];

    public $rememberTokenName = null;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isInternal(): bool
    {
        return $this->role === 'internal';
    }

    public function initials(): string
    {
        return strtoupper(mb_substr($this->name, 0, 2));
    }
}
