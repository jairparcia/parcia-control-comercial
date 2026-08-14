<?php

namespace App\Http\Middleware;

use App\Application\Subscription\GetSubscriptionStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresActiveSubscription
{
    public function __construct(
        private readonly GetSubscriptionStatusService $subscriptions,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->isInternal()) {
            return $next($request);
        }

        $status = $this->subscriptions->execute($user->id);

        if (! $status->isActive()) {
            return redirect()->route('billing');
        }

        return $next($request);
    }
}
