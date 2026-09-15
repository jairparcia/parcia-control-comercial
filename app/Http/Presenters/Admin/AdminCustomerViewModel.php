<?php

namespace App\Http\Presenters\Admin;

readonly class AdminCustomerViewModel
{
    public function __construct(
        public int     $id,
        public string  $name,
        public string  $email,
        public string  $description,
        public string  $country,
        public string  $createdAt,
    ) {}
}
