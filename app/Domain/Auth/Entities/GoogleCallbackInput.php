<?php

namespace App\Domain\Auth\Entities;

readonly class GoogleCallbackInput
{
    public function __construct(
        public string $googleId,
        public string $name,
        public string $email,
        public ?string $avatar,
    ) {}
}
