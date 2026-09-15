<?php

namespace App\Domain\Auth\Entities;

readonly class GoogleCallbackInputDTO
{
    public function __construct(
        public string $googleId,
        public string $name,
        public string $email,
        public ?string $avatar,
    ) {}
}
