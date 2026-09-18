<?php

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Application\Subscription\GetAvailablePlansService;
use App\Application\Subscription\GetSubscriptionStatusService;
use App\Domain\Subscription\Enums\Plan;
use App\Domain\Subscription\Enums\SubscriptionStatus;
use App\Domain\Subscription\Results\CheckoutSessionResult;
use App\Domain\Subscription\Results\PlanInfo;
use App\Domain\Subscription\Results\SubscriptionStatusResult;
use App\Livewire\OnboardingComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// ── Página de onboarding ───────────────────────────────────────────────────

it('redirects guests to login', function () {
    $this->get(route('onboarding'))->assertRedirect(route('login'));
});

it('renders onboarding page for a new user without a plan yet', function () {
    mockAvailablePlans();

    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertStatus(200);
});

it('redirects internal users away from onboarding', function () {
    $user = User::factory()->internal()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('dashboard'));
});

it('redirects already onboarded users away from onboarding', function () {
    $user = User::factory()->create(['onboarded_at' => now()]);

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('dashboard'));
});

it('redirects users with a real subscribed plan away from onboarding even if onboarded_at is null', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    mockSubscriptionStatus(new SubscriptionStatusResult(
        plan: Plan::Pro,
        status: SubscriptionStatus::Active,
        renewsAt: null,
        cancelledAt: null,
        pmType: null,
        pmLastFour: null,
    ));

    $this->actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('dashboard'));
});

// ── Empezar gratis ─────────────────────────────────────────────────────────

it('startFree marks onboarded_at and redirects to dashboard', function () {
    mockAvailablePlans();

    $user = User::factory()->create(['onboarded_at' => null]);

    Livewire::actingAs($user)
        ->test(OnboardingComponent::class)
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
        ->test(OnboardingComponent::class)
        ->call('choosePlan', 'starter')
        ->assertRedirect('https://stripe.com/c/pay/test');
});

it('choosePlan throws exception for invalid plan key', function () {
    mockAvailablePlans();

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OnboardingComponent::class)
        ->call('choosePlan', 'invalid_plan');
})->throws(RuntimeException::class);

it('choosePlan sends the checkout success url to onboarding.complete', function () {
    mockAvailablePlans();

    $this->mock(CreateCheckoutSessionService::class)
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn ($userId, $planKey, $successUrl, $cancelUrl) => $successUrl === route('onboarding.complete'))
        ->andReturn(new CheckoutSessionResult(checkoutUrl: 'https://stripe.com/c/pay/test'));

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(OnboardingComponent::class)
        ->call('choosePlan', 'starter');
});

// ── Completar onboarding tras checkout ──────────────────────────────────────

it('onboarding.complete marks onboarded_at and redirects to dashboard', function () {
    $user = User::factory()->create(['onboarded_at' => null]);

    $this->actingAs($user)
        ->get(route('onboarding.complete'))
        ->assertRedirect(route('dashboard'));

    expect($user->fresh()->onboarded_at)->not->toBeNull();
});

// ── Helper ─────────────────────────────────────────────────────────────────

function mockAvailablePlans(): void
{
    app()->instance(GetAvailablePlansService::class, new class extends GetAvailablePlansService
    {
        public function __construct() {}

        public function execute(): array
        {
            return [
                new PlanInfo(key: 'free', name: 'Gratuito', formattedPrice: 'Gratis', interval: 'month', currency: 'MXN', quota: 10),
                new PlanInfo(key: 'starter', name: 'Starter', formattedPrice: 'MX$100', interval: 'month', currency: 'MXN', quota: 500),
                new PlanInfo(key: 'pro', name: 'Pro', formattedPrice: 'MX$250', interval: 'month', currency: 'MXN', quota: 2000),
            ];
        }
    });
}

function mockSubscriptionStatus(SubscriptionStatusResult $status): void
{
    app()->instance(GetSubscriptionStatusService::class, new class($status) extends GetSubscriptionStatusService
    {
        public function __construct(private readonly SubscriptionStatusResult $status) {}

        public function execute(int $userId): SubscriptionStatusResult
        {
            return $this->status;
        }
    });
}
