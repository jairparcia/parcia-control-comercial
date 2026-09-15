<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── /admin/plans access control ───────────────────────────────────────────────

it('redirects unauthenticated users to login', function () {
    $this->get(route('admin.plans'))->assertRedirect(route('login'));
});

it('returns 403 for authenticated external users', function () {
    $user = User::factory()->create(['role' => 'external']);

    $this->actingAs($user)
        ->get(route('admin.plans'))
        ->assertForbidden();
});

it('renders the admin plans page for internal users', function () {
    $user = User::factory()->internal()->create();

    $this->actingAs($user)
        ->get(route('admin.plans'))
        ->assertOk();
});
