<?php

namespace App\Http\Controllers\Subscription;

use App\Application\Subscription\CreateCheckoutSessionService;
use App\Domain\Subscription\Entities\CreateCheckoutSessionInput;
use App\Domain\Subscription\Enums\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CreateCheckoutSessionController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutSessionService $service,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate(['plan' => ['required', 'string', 'in:starter,pro,agency']]);

        $result = $this->service->execute(new CreateCheckoutSessionInput(
            userId: $request->user()->id,
            plan: Plan::from($request->input('plan')),
            successUrl: route('dashboard') . '?subscribed=1',
            cancelUrl: route('billing'),
        ));

        return redirect()->away($result->checkoutUrl);
    }
}
