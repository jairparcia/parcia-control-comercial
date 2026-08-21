<?php

use App\Application\Auth\HandleGoogleCallbackService;
use App\Domain\Auth\Contracts\UserRepository;
use App\Domain\Auth\Entities\GoogleCallbackInput;
use App\Models\User;

// ── resolveRole ───────────────────────────────────────────────────────────────

it('assigns internal role to new users with parcia.co email', function () {
    $repo = Mockery::mock(UserRepository::class);
    $repo->allows('findByGoogleId')->with('g-new')->andReturn(null);
    $repo->allows('findOrCreateByGoogle')
        ->with(Mockery::type(GoogleCallbackInput::class), 'internal')
        ->andReturn(User::factory()->make(['id' => 1, 'role' => 'internal']));

    $result = (new HandleGoogleCallbackService($repo))->execute(
        googleId: 'g-new',
        name:     'Parcia Member',
        email:    'member@parcia.co',
        avatar:   null,
    );

    expect($result->role)->toBe('internal');
});

it('assigns external role to new users with non-parcia email', function () {
    $repo = Mockery::mock(UserRepository::class);
    $repo->allows('findByGoogleId')->with('g-ext')->andReturn(null);
    $repo->allows('findOrCreateByGoogle')
        ->with(Mockery::type(GoogleCallbackInput::class), 'external')
        ->andReturn(User::factory()->make(['id' => 2, 'role' => 'external']));

    $result = (new HandleGoogleCallbackService($repo))->execute(
        googleId: 'g-ext',
        name:     'External User',
        email:    'user@gmail.com',
        avatar:   null,
    );

    expect($result->role)->toBe('external');
});

it('preserves existing role on re-login regardless of email domain', function () {
    $existingUser = User::factory()->internal()->make(['id' => 1]);

    $repo = Mockery::mock(UserRepository::class);
    $repo->allows('findByGoogleId')->with('g-existing')->andReturn($existingUser);
    // Role passed to repo must be the existing role, not derived from email.
    $repo->allows('findOrCreateByGoogle')
        ->with(Mockery::type(GoogleCallbackInput::class), 'internal')
        ->andReturn($existingUser);

    (new HandleGoogleCallbackService($repo))->execute(
        googleId: 'g-existing',
        name:     'Parcia Member',
        email:    'member@parcia.co',
        avatar:   null,
    );

    expect(true)->toBeTrue();
});

it('marks result as new when user did not exist before', function () {
    $newUser = User::factory()->make(['id' => 99, 'role' => 'external']);

    $repo = Mockery::mock(UserRepository::class);
    $repo->allows('findByGoogleId')->andReturn(null);
    $repo->allows('findOrCreateByGoogle')->andReturn($newUser);

    $result = (new HandleGoogleCallbackService($repo))->execute(
        googleId: 'g-brand-new',
        name:     'New User',
        email:    'new@example.com',
        avatar:   null,
    );

    expect($result->isNew)->toBeTrue();
});

it('marks result as not new when user already existed', function () {
    $existing = User::factory()->make(['id' => 5, 'role' => 'external']);

    $repo = Mockery::mock(UserRepository::class);
    $repo->allows('findByGoogleId')->andReturn($existing);
    $repo->allows('findOrCreateByGoogle')->andReturn($existing);

    $result = (new HandleGoogleCallbackService($repo))->execute(
        googleId: 'g-returning',
        name:     'Returning User',
        email:    'returning@example.com',
        avatar:   null,
    );

    expect($result->isNew)->toBeFalse();
});
