<?php

namespace App\Http\Middleware;

use App\Application\Subscription\GetSubscriptionStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOnboarded
{
    public function __construct(
        private readonly GetSubscriptionStatusService $subscriptions,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $alreadyOnboarded = $user->isInternal()
            || $user->hasOnboarded()
            || $this->subscriptions->execute($user->id)->hasSubscribedPlan();

        if ($alreadyOnboarded) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
