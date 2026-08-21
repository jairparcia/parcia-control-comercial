<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Auth\Contracts\UserRepository;
use App\Http\Controllers\Controller;

class CompleteOnboardingController extends Controller
{
    public function __construct(private readonly UserRepository $users) {}

    public function __invoke()
    {
        $this->users->markOnboarded(auth()->id());

        return redirect()->route('dashboard');
    }
}
