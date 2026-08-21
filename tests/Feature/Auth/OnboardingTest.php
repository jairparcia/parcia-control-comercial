<?php

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Domain\Subscription\Results\CheckoutSessionResult;
use App\Domain\Subscription\Results\PlanInfo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Página de onboarding ───────────────────────────────────────────────────

it('redirects guests to login', function () {
    $this->get(route('onboarding'))->assertRedirect(route('login'));
});

it('renders onboarding page for authenticated users', function () {
    mockAvailablePlans();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertStatus(200);
});

// ── Empezar gratis ─────────────────────────────────────────────────────────

it('startFree marks onboarded_at and redirects to dashboard', function () {
    mockAvailablePlans();

    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)
        ->test(\App\Livewire\OnboardingComponent::class)
        ->call('startFree')
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->onboarded_at)->not->toBeNull();
});

// ── Elegir plan de pago ────────────────────────────────────────────────────

it('choosePlan redirects to stripe checkout url', function () {
    mockAvailablePlans();

    $this->mock(CreateCheckoutSessionService::class)
        ->shouldReceive('execute')
        ->once()
        ->andReturn(new CheckoutSessionResult(checkoutUrl: 'https://stripe.com/c/pay/test'));

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\OnboardingComponent::class)
        ->call('choosePlan', 'starter')
        ->assertRedirect('https://stripe.com/c/pay/test');
});

it('choosePlan throws exception for invalid plan key', function () {
    mockAvailablePlans();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(\App\Livewire\OnboardingComponent::class)
        ->call('choosePlan', 'invalid_plan');
})->throws(\RuntimeException::class);

// ── Helper ─────────────────────────────────────────────────────────────────

function mockAvailablePlans(): void
{
    app()->instance(GetAvailablePlansService::class, new class extends GetAvailablePlansService {
        public function __construct() {}
        public function execute(): array
        {
            return [
                new PlanInfo(key: 'free',    name: 'Gratuito', formattedPrice: 'Gratis',  interval: 'month', currency: 'MXN', quota: 10),
                new PlanInfo(key: 'starter', name: 'Starter',  formattedPrice: 'MX$100', interval: 'month', currency: 'MXN', quota: 500),
                new PlanInfo(key: 'pro',     name: 'Pro',      formattedPrice: 'MX$250', interval: 'month', currency: 'MXN', quota: 2000),
            ];
        }
    });
}
