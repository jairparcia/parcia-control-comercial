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

    private function resolveRole(string $email, ?string $existingRole): string
    {
        // Existing users keep their role — it may have been promoted manually via admin.
        if ($existingRole !== null) {
            return $existingRole;
        }

        return str_ends_with($email, '@parcia.co') ? 'internal' : 'external';
    }

    public function execute(
        string $googleId,
        string $name,
        string $email,
        ?string $avatar,
    ): AuthenticatedUserResult {
        $existing = $this->users->findByGoogleId($googleId);

        $role = $this->resolveRole($email, $existing?->role);

        $input = new GoogleCallbackInput(
            googleId: $googleId,
            name:     $name,
            email:    $email,
            avatar:   $avatar,
        );

        $user = $this->users->findOrCreateByGoogle($input, $role);

        Auth::login($user);

        return new AuthenticatedUserResult(
            userId:       $user->id,
            name:         $user->name,
            email:        $user->email,
            role:         $user->role,
            isNew:        $existing === null,
            hasOnboarded: $user->hasOnboarded(),
        );
    }
}
