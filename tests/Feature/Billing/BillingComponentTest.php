<?php

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Domain\Subscription\Results\CheckoutSessionResult;
use App\Domain\Subscription\Results\PlanInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()->instance(GetAvailablePlansService::class, new class extends GetAvailablePlansService {
        public function __construct() {}
        public function execute(): array
        {
            return [
                new PlanInfo(key: 'starter', name: 'Starter', formattedPrice: 'MX$100', interval: 'month', currency: 'MXN', quota: 500),
                new PlanInfo(key: 'pro',     name: 'Pro',     formattedPrice: 'MX$250', interval: 'month', currency: 'MXN', quota: 2000),
            ];
        }
    });
});

// ── Acceso ────────────────────────────────────────────────────────────────

it('redirects guests to login', function () {
    $this->get(route('billing'))->assertRedirect(route('login'));
});

it('renders billing page for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('billing'))->assertStatus(200);
});

// ── Checkout ──────────────────────────────────────────────────────────────

it('checkout action redirects to stripe url', function () {
    $this->mock(CreateCheckoutSessionService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(new CheckoutSessionResult(checkoutUrl: 'https://stripe.com/c/pay/test'));

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\BillingComponent::class)
        ->call('checkout', 'pro')
        ->assertRedirect('https://stripe.com/c/pay/test');
});

it('checkout throws exception for invalid plan key', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\BillingComponent::class)
        ->call('checkout', 'plan_invalido');
})->throws(\RuntimeException::class);
