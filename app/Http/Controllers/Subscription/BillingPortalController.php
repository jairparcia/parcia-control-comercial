<?php

namespace App\Http\Controllers\Subscription;

use App\Application\Subscription\GetBillingPortalUrlService;
use App\Application\Subscription\GetSubscriptionStatusService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingPortalController extends Controller
{
    public function __construct(
        private readonly GetSubscriptionStatusService $statusService,
        private readonly GetBillingPortalUrlService $portalService,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $status = $this->statusService->execute($request->user()->id);

        if (! $status->isActive()) {
            return redirect()->route('billing');
        }

        $url = $this->portalService->execute($request->user()->id, route('billing'));

        return redirect()->away($url);
    }
}
