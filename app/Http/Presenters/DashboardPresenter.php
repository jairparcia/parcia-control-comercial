<?php

namespace App\Http\Presenters;

class DashboardPresenter
{
    // ── Hardcoded — se reemplazarán con Result DTOs de los services ───────
    //
    // public function __construct(
    //     QuotaCheckResult $quota,
    //     SubscriptionStatusResult $subscription,
    //     CreatorTableResult $recentCreators,
    //     LicenseKeyResult $licenseKey,
    // ) {}

    // ── Usage ─────────────────────────────────────────────────────────────

    public function scansUsed(): string
    {
        return number_format(342);
    }

    public function scansLimit(): string
    {
        return number_format(500);
    }

    public function scansRemaining(): string
    {
        return number_format(158);
    }

    public function scansPercent(): float
    {
        return 68.4;
    }

    public function quotaBarColor(): string
    {
        $pct = $this->scansPercent();

        return match (true) {
            $pct >= 90 => 'bg-[#ef4444]',
            $pct >= 70 => 'bg-[#f59e0b]',
            default    => 'bg-[#5b69e2]',
        };
    }

    public function periodLabel(): string
    {
        return '1–31 agosto 2026 · Se reinicia en 26 días';
    }

    // ── Stats ─────────────────────────────────────────────────────────────

    public function totalCreators(): string
    {
        return number_format(128);
    }

    public function totalPublications(): string
    {
        return number_format(2847);
    }

    // ── Subscription ──────────────────────────────────────────────────────

    public function planName(): string
    {
        return 'Starter';
    }

    public function planExpiry(): string
    {
        return 'ago 31';
    }

    public function isUpgradeable(): bool
    {
        return in_array($this->planName(), ['Starter']);
    }

    public function upgradeWarning(): string
    {
        return "Estás al {$this->scansPercent()}% de tu cuota mensual";
    }

    // ── Recent creators ───────────────────────────────────────────────────

    public function recentCreators(): array
    {
        // Populated by: GetCreatorsTableService::execute(CreatorTableFiltersInput)
        return [
            ['initials' => 'MR', 'handle' => '@mariaruiz',     'name' => 'María Ruiz',    'platform' => 'tiktok',    'bg' => '#e9dfcf', 'color' => '#5c4a36', 'followers' => '1.2M'],
            ['initials' => 'JG', 'handle' => '@jorge.garcia',  'name' => 'Jorge García',  'platform' => 'instagram', 'bg' => '#fce7f3', 'color' => '#9d174d', 'followers' => '847K'],
            ['initials' => 'LV', 'handle' => '@luisavieira',   'name' => 'Luisa Vieira',  'platform' => 'tiktok',    'bg' => '#dbeafe', 'color' => '#1e40af', 'followers' => '412K'],
            ['initials' => 'CP', 'handle' => '@carla.perez',   'name' => 'Carla Pérez',   'platform' => 'instagram', 'bg' => '#fce7f3', 'color' => '#9d174d', 'followers' => '289K'],
            ['initials' => 'AM', 'handle' => '@andres.moreno', 'name' => 'Andrés Moreno', 'platform' => 'tiktok',    'bg' => '#f0fdf4', 'color' => '#15803d', 'followers' => '178K'],
        ];
    }

    // ── License key ───────────────────────────────────────────────────────

    public function licenseKey(): string
    {
        // Populated by: GenerateLicenseKeyService / ValidateLicenseKeyService
        return 'pk_live_a1b2c3d4-e5f6-7890-abcd-ef1234567890';
    }

    public function licenseActive(): bool
    {
        return true;
    }

    public function licenseStatusLabel(): string
    {
        return $this->licenseActive() ? 'Activa' : 'Inactiva';
    }

    public function licenseStatusColor(): string
    {
        return $this->licenseActive() ? 'text-[#16a34a]' : 'text-[#dc2626]';
    }
}
