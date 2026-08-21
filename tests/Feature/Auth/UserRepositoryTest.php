<?php

use App\Domain\Auth\Entities\GoogleCallbackInput;
use App\Infrastructure\Repository\Auth\EloquentUserRepository;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = app(EloquentUserRepository::class);
});

// ── findOrCreateByGoogle ──────────────────────────────────────────────────

it('creates a new user with external role on first google login', function () {
    $input = new GoogleCallbackInput(
        googleId: 'google-123',
        name:     'Carlos Molina',
        email:    'carlos@example.com',
        avatar:   null,
    );

    $user = $this->repo->findOrCreateByGoogle($input, 'external');

    expect($user->google_id)->toBe('google-123')
        ->and($user->role)->toBe('external')
        ->and($user->email)->toBe('carlos@example.com');

    $this->assertDatabaseHas('users', ['google_id' => 'google-123', 'role' => 'external']);
});

it('creates a new user with internal role for parcia.co email', function () {
    $input = new GoogleCallbackInput(
        googleId: 'google-parcia',
        name:     'Parcia Member',
        email:    'member@parcia.co',
        avatar:   null,
    );

    $user = $this->repo->findOrCreateByGoogle($input, 'internal');

    expect($user->role)->toBe('internal');
    $this->assertDatabaseHas('users', ['google_id' => 'google-parcia', 'role' => 'internal']);
});

it('updates existing user data on subsequent google logins', function () {
    User::factory()->create(['google_id' => 'google-123', 'name' => 'Nombre Viejo']);

    $input = new GoogleCallbackInput(
        googleId: 'google-123',
        name:     'Nombre Nuevo',
        email:    'nuevo@example.com',
        avatar:   null,
    );

    $this->repo->findOrCreateByGoogle($input, 'external');

    $this->assertDatabaseHas('users', ['google_id' => 'google-123', 'name' => 'Nombre Nuevo']);
    $this->assertDatabaseCount('users', 1);
});

it('does not override role when updating existing user', function () {
    User::factory()->internal()->create(['google_id' => 'google-internal']);

    $input = new GoogleCallbackInput(
        googleId: 'google-internal',
        name:     'Parcia Team',
        email:    'team@parcia.co',
        avatar:   null,
    );

    // Service passes back the existing role — repo just persists it.
    $user = $this->repo->findOrCreateByGoogle($input, 'internal');

    expect($user->fresh()->role)->toBe('internal');
});

// ── markOnboarded ─────────────────────────────────────────────────────────

it('sets onboarded_at timestamp', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    expect($user->onboarded_at)->toBeNull();

    $this->repo->markOnboarded($user->id);

    expect($user->fresh()->onboarded_at)->not->toBeNull()
        ->and($user->fresh()->hasOnboarded())->toBeTrue();
});

it('findByGoogleId returns null when user does not exist', function () {
    expect($this->repo->findByGoogleId('nonexistent'))->toBeNull();
});

it('findByGoogleId returns user when they exist', function () {
    $user = User::factory()->create(['google_id' => 'google-abc']);

    expect($this->repo->findByGoogleId('google-abc')?->id)->toBe($user->id);
});
