<?php

namespace App\Http\Presenters;

readonly class NotificationViewModel
{
    public function __construct(
        public int    $id,
        public string $title,
        public string $body,
        public bool   $isRead,
        public string $createdAt,
    ) {}
}
