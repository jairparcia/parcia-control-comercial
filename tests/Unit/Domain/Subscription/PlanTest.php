<?php

use App\Domain\Subscription\Enums\Plan;

it('returns correct label for each plan', function () {
    expect(Plan::Free->label())->toBe('Gratuito')
        ->and(Plan::Starter->label())->toBe('Starter')
        ->and(Plan::Pro->label())->toBe('Pro')
        ->and(Plan::Agency->label())->toBe('Agency')
        ->and(Plan::Internal->label())->toBe('Internal');
});

it('isPaid returns false for free and internal plans', function () {
    expect(Plan::Free->isPaid())->toBeFalse()
        ->and(Plan::Internal->isPaid())->toBeFalse()
        ->and(Plan::Starter->isPaid())->toBeTrue()
        ->and(Plan::Pro->isPaid())->toBeTrue()
        ->and(Plan::Agency->isPaid())->toBeTrue();
});

it('only pro, agency and internal plans support webhooks', function () {
    expect(Plan::Free->supportsWebhooks())->toBeFalse()
        ->and(Plan::Starter->supportsWebhooks())->toBeFalse()
        ->and(Plan::Pro->supportsWebhooks())->toBeTrue()
        ->and(Plan::Agency->supportsWebhooks())->toBeTrue()
        ->and(Plan::Internal->supportsWebhooks())->toBeTrue();
});

it('can be created from its string value', function () {
    expect(Plan::from('free'))->toBe(Plan::Free)
        ->and(Plan::from('pro'))->toBe(Plan::Pro)
        ->and(Plan::from('internal'))->toBe(Plan::Internal);
});
