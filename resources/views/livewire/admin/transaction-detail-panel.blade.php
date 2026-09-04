<div x-data="{ open: @entangle('panelOpen') }" @keydown.escape.window="if (open) $wire.close()">
    <x-slide-over :title="__('admin.transaction')" max-width="max-w-2xl" close-action="$wire.close()">
        <x-slot:badge>
            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full {{ $statusBadgeClass }}">
                {{ $statusLabel }}
            </span>
        </x-slot:badge>

        <div class="px-6 py-6">

        {{-- ── Header ─────────────────────────────────────────────────────────── --}}
        <div class="mb-6">
            <p class="text-3xl font-semibold text-white tracking-tight">{{ $formattedAmount }}</p>
            @if ($customerName)
                <p class="text-sm text-[#71717a] mt-1">
                    {{ __('admin.charged_to') }}
                    <span class="text-[#a1a1aa]">{{ $customerName }}</span>
                </p>
            @endif
        </div>

        {{-- ── Recent activity (timeline) ────────────────────────────────────── --}}
        @if ($events)
            <section class="mb-6">
                <h2 class="text-sm font-medium text-white mb-3">{{ __('admin.recent_activity') }}</h2>
                <ol class="relative border-l border-[#3f3f46] ml-2">
                    @foreach ($events as $event)
                        <li class="mb-4 ml-5 last:mb-0">
                            @if ($loop->first)
                                <span class="absolute -left-[7px] w-3.5 h-3.5 rounded-full bg-violet-500 ring-2 ring-[#18181b]"></span>
                            @else
                                <span class="absolute -left-[7px] w-3.5 h-3.5 rounded-full bg-[#18181b] border-2 border-[#52525b]"></span>
                            @endif
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm {{ $loop->first ? 'text-white font-medium' : 'text-[#a1a1aa]' }}">{{ $event['description'] }}</p>
                                <p class="text-xs text-[#52525b] whitespace-nowrap">{{ $event['time'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <div class="border-t border-[#27272a] my-6"></div>

        {{-- ── Purchase summary ───────────────────────────────────────────────── --}}
        <section class="mb-6">
            <h2 class="text-sm font-medium text-white mb-3">{{ __('admin.purchase_summary') }}</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-[#52525b] font-medium">{{ __('common.customer') }}</dt>
                    <dd class="text-[#a1a1aa] mt-0.5">
                        @if ($customerEmail)
                            <div>{{ $customerEmail }}</div>
                        @endif
                        @if ($billingName)
                            <div>{{ $billingName }}</div>
                        @endif
                        @if ($billingCountry)
                            <div>{{ $billingCountry }}</div>
                        @endif
                        @if (! $customerEmail && ! $billingName)
                            —
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-[#52525b] font-medium">{{ __('common.subscription') }}</dt>
                    <dd class="text-[#a1a1aa] mt-0.5">
                        @if ($planName || $priceId)
                            @if ($planName)
                                <div>{{ $planName }}</div>
                            @endif
                            @if ($priceId)
                                <div class="text-xs font-mono break-all mt-0.5">{{ $priceId }}</div>
                            @endif
                        @else
                            —
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-[#52525b] font-medium">{{ __('admin.subscription_id') }}</dt>
                    <dd class="text-[#a1a1aa] mt-0.5 text-xs font-mono break-all">{{ $subscriptionId ?? '—' }}</dd>
                </div>

                <div>
                    <dt class="text-[#52525b] font-medium">{{ __('admin.invoice_id') }}</dt>
                    <dd class="text-[#a1a1aa] mt-0.5">{{ $invoiceNumber ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <div class="border-t border-[#27272a] my-6"></div>

        {{-- ── Payment breakdown ──────────────────────────────────────────────── --}}
        <section class="mb-6">
            <h2 class="text-sm font-medium text-white mb-3">{{ __('admin.payment_breakdown') }}</h2>
            <div class="bg-[#09090b] rounded-lg border border-[#27272a] divide-y divide-[#27272a] text-sm">

                <div class="flex items-center justify-between px-4 py-3">
                    <span class="text-[#a1a1aa]">{{ __('admin.amount_charged') }}</span>
                    <span class="text-white font-medium tabular-nums">{{ $formattedAmount }}</span>
                </div>

                <div x-data="{ feeOpen: false }">
                    <button
                        type="button"
                        @click="feeOpen = !feeOpen"
                        class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-[#27272a]/40 transition-colors"
                    >
                        <span class="flex items-center gap-1.5 text-[#a1a1aa]">
                            <svg
                                class="w-3.5 h-3.5 text-[#52525b] transition-transform duration-200"
                                :class="feeOpen ? 'rotate-90' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            {{ __('admin.stripe_fee') }}
                        </span>
                        <span class="text-red-400 tabular-nums">−{{ $formattedFees }}</span>
                    </button>

                    <div
                        x-show="feeOpen"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-1"
                        class="border-t border-[#27272a]"
                    >
                        @forelse ($feeDetails as $detail)
                            <div class="flex items-center justify-between px-6 py-2">
                                <span class="text-xs text-[#52525b]">{{ $detail['description'] }}</span>
                                <span class="text-xs text-[#71717a] tabular-nums">−{{ $detail['amount'] }}</span>
                            </div>
                        @empty
                            <div class="px-6 py-2">
                                <span class="text-xs text-[#52525b]">{{ __('admin.no_breakdown') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center justify-between px-4 py-3 bg-[#18181b] rounded-b-lg">
                    <span class="text-white font-medium">{{ __('admin.net_amount') }}</span>
                    <span class="text-emerald-400 font-medium tabular-nums">{{ $formattedNet }}</span>
                </div>
            </div>
        </section>

        <div class="border-t border-[#27272a] my-6"></div>

        {{-- ── Payment method ─────────────────────────────────────────────────── --}}
        <section class="mb-6">
            <h2 class="text-sm font-medium text-white mb-3">{{ __('common.payment_method') }}</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                @if ($paymentMethodId)
                    <div>
                        <dt class="text-[#52525b] font-medium">ID</dt>
                        <dd class="text-[#a1a1aa] mt-0.5 text-xs font-mono break-all">{{ $paymentMethodId }}</dd>
                    </div>
                @endif

                @if ($cardDisplay)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.number') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $cardDisplay }}</dd>
                    </div>
                @endif

                @if ($cardFingerprint)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.fingerprint') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5 text-xs font-mono">{{ $cardFingerprint }}</dd>
                    </div>
                @endif

                @if ($cardExpiry)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.expires') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $cardExpiry }}</dd>
                    </div>
                @endif

                @if ($cardType)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.type') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5 capitalize">{{ $cardType }}</dd>
                    </div>
                @endif

                @if ($cardIssuer)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.issuer') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $cardIssuer }}</dd>
                    </div>
                @endif

                @if ($billingName)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.cardholder') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $billingName }}</dd>
                    </div>
                @endif

                @if ($billingEmail)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.cardholder_email') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $billingEmail }}</dd>
                    </div>
                @endif

                @if ($billingCountry)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.address') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $billingCountry }}</dd>
                    </div>
                @endif

                @if ($cardCountry)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.card_origin') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5">{{ $cardCountry }}</dd>
                    </div>
                @endif

                @if ($cvcCheckLabel)
                    <div>
                        <dt class="text-[#52525b] font-medium">{{ __('admin.cvc_check') }}</dt>
                        <dd class="mt-0.5 {{ $cvcCheckClass }}">
                            {{ $cvcCheckLabel }}
                            @if ($cvcCheckPassed)
                                ✓
                            @endif
                        </dd>
                    </div>
                @endif

                @if ($paymentIntentId)
                    <div class="col-span-2">
                        <dt class="text-[#52525b] font-medium">{{ __('admin.reusable_payment_id') }}</dt>
                        <dd class="text-[#a1a1aa] mt-0.5 text-xs font-mono break-all">{{ $paymentIntentId }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        {{-- ── Stripe ID ──────────────────────────────────────────────────────── --}}
        <div class="border-t border-[#27272a] pt-4">
            <p class="text-xs text-[#52525b]">
                Charge ID: <span class="font-mono">{{ $stripeId }}</span>
                &nbsp;·&nbsp; {{ $date }}
            </p>
        </div>

        </div>{{-- /px-6 py-6 --}}
    </x-slide-over>
</div>
