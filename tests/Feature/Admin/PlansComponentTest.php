<?php

use App\Livewire\Admin\PlansComponent;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Listing ───────────────────────────────────────────────────────────────────

it('renders existing plans in the table', function () {
    SubscriptionPlan::create([
        'key' => 'starter', 'name' => 'Starter', 'unit_amount' => 15000,
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 500,
        'sort_order' => 1, 'active' => true,
    ]);

    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlansComponent::class)
        ->assertSee('Starter');
});

// ── Toggle ────────────────────────────────────────────────────────────────────

it('toggles plan active status', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'starter', 'name' => 'Starter', 'unit_amount' => 15000,
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 500,
        'sort_order' => 1, 'active' => true,
    ]);

    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlansComponent::class)
        ->call('toggle', $plan->id);

    expect($plan->fresh()->active)->toBeFalse();

    Livewire::actingAs($user)
        ->test(PlansComponent::class)
        ->call('toggle', $plan->id);

    expect($plan->fresh()->active)->toBeTrue();
});
