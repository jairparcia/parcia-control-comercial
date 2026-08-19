<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\HandleGoogleCallbackService;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;

class GoogleCallbackController extends Controller
{
    public function __construct(
        private readonly HandleGoogleCallbackService $service,
    ) {}

    public function __invoke()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $result = $this->service->execute(
            googleId: $googleUser->getId(),
            name:     $googleUser->getName(),
            email:    $googleUser->getEmail(),
            avatar:   $googleUser->getAvatar(),
        );

        if ($result->role === 'internal') {
            return redirect()->route('dashboard');
        }

        return $result->hasOnboarded
            ? redirect()->route('dashboard')
            : redirect()->route('onboarding');
    }
}
