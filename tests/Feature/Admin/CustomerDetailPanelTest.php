<?php

use App\Livewire\Admin\CustomerDetailPanel;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

function panelSubscriptionFor(User $user, string $status = 'active'): void
{
    Subscription::create([
        'user_id'       => $user->id,
        'type'          => 'default',
        'stripe_id'     => 'sub_' . uniqid(),
        'stripe_status' => $status,
        'stripe_price'  => 'price_test',
        'quantity'      => 1,
    ]);
}

// --- Panel: open ---

it('loads the customer data into the panel on openPanel', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->assertSet('panelOpen', true)
        ->assertSet('panelName', 'Jane Doe')
        ->assertSet('panelEmail', 'jane@example.com')
        ->assertSet('selectedId', $customer->id);
});

it('marks hasSub false when the customer has no subscription', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->assertSet('hasSub', false);
});

it('marks hasSub true when the customer has an active subscription', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    panelSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->assertSet('hasSub', true);
});

it('marks panelArchived correctly when opening an archived customer', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create(['archived' => true]);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->assertSet('panelArchived', true);
});

// --- Archive: no active subscription → archives immediately ---

it('archives a customer without an active subscription immediately', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')
        ->assertSet('archiveModalOpen', false)
        ->assertSet('panelOpen', false);

    expect(User::find($customer->id)->archived)->toBeTrue();
});

// --- Archive: with active subscription → confirmation modal ---

it('shows the confirmation modal when archiving a customer with an active subscription', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    panelSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')
        ->assertSet('archiveModalOpen', true);

    expect(User::find($customer->id)->archived)->toBeFalse();
});

it('does not archive the customer when the modal is opened', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    panelSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer');

    expect(User::find($customer->id)->archived)->toBeFalse();
});

it('archives after confirmArchive is called', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    panelSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')
        ->call('confirmArchive')
        ->assertSet('panelOpen', false);

    expect(User::find($customer->id)->archived)->toBeTrue();
});

it('closes the modal without archiving on closeArchiveModal', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    panelSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')
        ->call('closeArchiveModal')
        ->assertSet('archiveModalOpen', false);

    expect(User::find($customer->id)->archived)->toBeFalse();
});

// --- Restore ---

it('restores an archived customer and closes the panel', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create(['archived' => true]);

    Livewire::actingAs($admin)
        ->test(CustomerDetailPanel::class)
        ->call('openPanel', $customer->id)
        ->call('restoreCustomer')
        ->assertSet('panelOpen', false);

    expect(User::find($customer->id)->archived)->toBeFalse();
});
