<?php

use App\Livewire\Admin\CustomersComponent;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

// Creates an active Subscription row for the given user (no stripe_id on user,
// so GetCustomerDetailService will not call the Stripe gateway).
function activeSubscriptionFor(User $user, string $status = 'active'): void
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

// --- Access control ---

it('redirects guests away from the admin customers page', function () {
    $this->get(route('admin.customers'))->assertRedirect('/login');
});

it('forbids external users from the admin customers page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.customers'))
        ->assertForbidden();
});

it('allows internal users to view the admin customers page', function () {
    $this->actingAs(User::factory()->internal()->create())
        ->get(route('admin.customers'))
        ->assertOk();
});

// --- Panel: open ---

it('loads the customer data into the panel on openPanel', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
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
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->assertSet('hasSub', false);
});

it('marks hasSub true when the customer has an active subscription', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    activeSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->assertSet('hasSub', true);
});

// --- Archive: no active subscription → archives immediately ---

it('archives a customer without an active subscription immediately', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
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
    activeSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')
        ->assertSet('archiveModalOpen', true);

    expect(User::find($customer->id)->archived)->toBeFalse();
});

it('does not archive the customer when the modal is opened', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    activeSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer'); // opens modal, does NOT archive

    expect(User::find($customer->id)->archived)->toBeFalse();
});

it('archives after confirmArchive is called', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    activeSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->call('archiveCustomer')     // opens modal
        ->call('confirmArchive')      // actually archives
        ->assertSet('panelOpen', false);

    expect(User::find($customer->id)->archived)->toBeTrue();
});

it('closes the modal without archiving on closeArchiveModal', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create();
    activeSubscriptionFor($customer);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
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
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->call('restoreCustomer')
        ->assertSet('panelOpen', false);

    expect(User::find($customer->id)->archived)->toBeFalse();
});

it('marks panelArchived correctly when opening an archived customer', function () {
    $admin    = User::factory()->internal()->create();
    $customer = User::factory()->create(['archived' => true]);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->call('openPanel', $customer->id)
        ->assertSet('panelArchived', true);
});

// --- Status filter ---

it('defaults to the active status filter on mount', function () {
    $admin = User::factory()->internal()->create();

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->assertSet('statusFilter', 'active');
});

it('only shows active customers by default', function () {
    $admin    = User::factory()->internal()->create();
    $active   = User::factory()->create(['name' => 'Active User']);
    $inactive = User::factory()->create(['name' => 'Inactive User']);
    $archived = User::factory()->create(['name' => 'Archived User', 'archived' => true]);

    activeSubscriptionFor($active);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->assertSee('Active User')
        ->assertDontSee('Inactive User')
        ->assertDontSee('Archived User');
});

it('shows archived customers when the archived filter is selected', function () {
    $admin    = User::factory()->internal()->create();
    $active   = User::factory()->create(['name' => 'Active User']);
    $archived = User::factory()->create(['name' => 'Archived User', 'archived' => true]);

    activeSubscriptionFor($active);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->set('statusFilter', 'archived')
        ->assertSee('Archived User')
        ->assertDontSee('Active User');
});

it('shows all customers including archived when all filter is selected', function () {
    $admin    = User::factory()->internal()->create();
    $active   = User::factory()->create(['name' => 'Active User']);
    $archived = User::factory()->create(['name' => 'Archived User', 'archived' => true]);

    activeSubscriptionFor($active);

    Livewire::actingAs($admin)
        ->test(CustomersComponent::class)
        ->set('statusFilter', 'all')
        ->assertSee('Active User')
        ->assertSee('Archived User');
});
