<?php

namespace App\Domain\Admin\Results;

readonly class AdminCustomerResult
{
    public function __construct(
        public int                $id,
        public string             $name,
        public string             $email,
        public ?string            $description,
        public ?string            $country,
        public bool               $archived,
        public bool               $hasActiveSub,
        public \DateTimeImmutable $createdAt,
    ) {}
}
