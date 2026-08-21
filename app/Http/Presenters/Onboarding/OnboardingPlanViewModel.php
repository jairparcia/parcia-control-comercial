<?php

namespace App\Http\Presenters\Onboarding;

readonly class OnboardingPlanViewModel
{
    public function __construct(
        public string $key,
        public string $name,
        public string $formattedPrice,
        public string $formattedIntervalLabel,
        public bool   $isFree,
        public bool   $isPro,
        public array  $features,
        public string $cardBorderClass,
        public string $featureCheckClass,
        public string $buttonClass,
    ) {}
}
