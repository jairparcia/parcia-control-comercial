<?php

namespace App\Domain\Auth\Results;

readonly class AuthenticatedUserResult
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public string $role,
        public bool $isNew,
        public bool $hasOnboarded,
    ) {}
}
