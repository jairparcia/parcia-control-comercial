<?php

namespace App\Http\Controllers\Auth;

use App\Application\Auth\HandleGoogleCallbackService;
use App\Domain\Auth\Entities\GoogleCallbackInput;
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

        $input = new GoogleCallbackInput(
            googleId: $googleUser->getId(),
            name:     $googleUser->getName(),
            email:    $googleUser->getEmail(),
            avatar:   $googleUser->getAvatar(),
        );

        $result = $this->service->execute($input);

        return $result->isNew
            ? redirect()->route('onboarding')
            : redirect()->route('dashboard');
    }
}
