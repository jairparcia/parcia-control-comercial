<?php

namespace App\Domain\Admin\Results;

readonly class TransactionEventResult
{
    public function __construct(
        public string            $description,
        public \DateTimeImmutable $happenedAt,
    ) {}
}
