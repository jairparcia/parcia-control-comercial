<?php

use App\Livewire\Admin\CustomersComponent;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

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
