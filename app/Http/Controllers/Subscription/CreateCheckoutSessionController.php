<?php

namespace App\Http\Controllers\Subscription;

use App\Application\Subscription\CreateCheckoutSessionService;
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

        $result = $this->service->execute(
            userId:     $request->user()->id,
            planKey:    $request->input('plan'),
            successUrl: route('dashboard') . '?subscribed=1',
            cancelUrl:  route('billing'),
        );

        return redirect()->away($result->checkoutUrl);
    }
}
