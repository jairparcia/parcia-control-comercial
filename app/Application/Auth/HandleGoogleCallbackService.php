<?php

namespace App\Application\Auth;

use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Auth\Entities\GoogleCallbackInput;
use App\Domain\Auth\Results\AuthenticatedUserResult;
use Illuminate\Support\Facades\Auth;

class HandleGoogleCallbackService
{
    public function __construct(
        private readonly UserRepository $users,
    ) {}

    public function execute(GoogleCallbackInput $input): AuthenticatedUserResult
    {
        $existing = $this->users->findByGoogleId($input->googleId);

        $user = $this->users->findOrCreateByGoogle($input);

        Auth::login($user);

        return new AuthenticatedUserResult(
            userId: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            isNew: $existing === null,
        );
    }
}
