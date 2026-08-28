<?php

use App\Livewire\Admin\PlanFormPanel;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Livewire\Livewire;

// ── Create ────────────────────────────────────────────────────────────────────

it('creates a free plan without calling stripe', function () {
    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', null)
        ->set('formName', 'Basic')
        ->set('formKey', 'basic')
        ->set('formDescription', 'Entry level plan')
        ->set('formFeatures', "TikTok & Instagram\nCSV export")
        ->set('formQuota', 200)
        ->set('formUnitAmount', 0)
        ->set('formCurrency', 'MXN')
        ->set('formInterval', 'month')
        ->set('formSortOrder', 1)
        ->call('save');

    $plan = SubscriptionPlan::where('key', 'basic')->first();

    expect($plan)->not->toBeNull()
        ->and($plan->name)->toBe('Basic')
        ->and($plan->quota)->toBe(200)
        ->and($plan->features)->toBe(['TikTok & Instagram', 'CSV export'])
        ->and($plan->stripe_price_id)->toBeNull();
});

it('validates required fields on create', function () {
    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', null)
        ->call('save')
        ->assertHasErrors(['formName', 'formKey']);
});

it('rejects duplicate plan key', function () {
    SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 30000,
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 2000,
        'sort_order' => 2, 'active' => true,
    ]);

    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', null)
        ->set('formName', 'Pro Duplicate')
        ->set('formKey', 'pro')
        ->call('save')
        ->assertHasErrors(['formKey']);
});

it('opens the panel in create mode when id is null', function () {
    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', null)
        ->assertSet('panelOpen', true)
        ->assertSet('isEditing', false)
        ->assertSet('editingId', null);
});

// ── Edit ──────────────────────────────────────────────────────────────────────

it('updates plan name and features', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'starter', 'name' => 'Starter', 'unit_amount' => 15000,
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 500,
        'sort_order' => 1, 'active' => true, 'features' => ['CSV export'],
    ]);

    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', $plan->id)
        ->set('formName', 'Starter Plus')
        ->set('formFeatures', "CSV export\nWebhooks\nPriority support")
        ->call('save');

    $plan->refresh();

    expect($plan->name)->toBe('Starter Plus')
        ->and($plan->features)->toBe(['CSV export', 'Webhooks', 'Priority support']);
});

it('populates form fields correctly when editing', function () {
    $plan = SubscriptionPlan::create([
        'key' => 'pro', 'name' => 'Pro', 'unit_amount' => 30000,
        'currency' => 'MXN', 'interval' => 'month', 'quota' => 2000,
        'sort_order' => 2, 'active' => true,
        'features' => ['Webhooks', 'Priority support'],
    ]);

    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', $plan->id)
        ->assertSet('panelOpen', true)
        ->assertSet('isEditing', true)
        ->assertSet('formName', 'Pro')
        ->assertSet('formKey', 'pro')
        ->assertSet('formUnitAmount', 300)
        ->assertSet('formFeatures', "Webhooks\nPriority support");
});

// ── Features parsing ──────────────────────────────────────────────────────────

it('ignores blank lines in features textarea', function () {
    $user = User::factory()->internal()->create();

    Livewire::actingAs($user)
        ->test(PlanFormPanel::class)
        ->call('openPanel', null)
        ->set('formName', 'Test Plan')
        ->set('formKey', 'test-plan')
        ->set('formFeatures', "Feature A\n\nFeature B\n  \nFeature C")
        ->set('formQuota', 100)
        ->set('formUnitAmount', 0)
        ->set('formSortOrder', 5)
        ->call('save');

    expect(SubscriptionPlan::where('key', 'test-plan')->first()->features)
        ->toBe(['Feature A', 'Feature B', 'Feature C']);
});
