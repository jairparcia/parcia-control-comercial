<?php

namespace App\Livewire\Layout;

use App\Application\Subscription\GetSubscriptionStatusService;
use Livewire\Component;

class AdminSidebar extends Component
{
    public bool $collapsed = false;
    public string $active = 'dashboard';

    private GetSubscriptionStatusService $statusService;

    public function boot(GetSubscriptionStatusService $statusService): void
    {
        $this->statusService = $statusService;
    }

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    public function render()
    {
        $user = auth()->user();

        $navItems = [
            [
                'key'   => 'dashboard',
                'label' => 'Dashboard',
                'route' => route('dashboard'),
                'icon'  => 'home',
            ],
            [
                'key'   => 'creators',
                'label' => 'Mis Creadores',
                'route' => '#',
                'icon'  => 'creators',
            ],
            [
                'key'   => 'billing',
                'label' => 'Facturación',
                'route' => route('billing'),
                'icon'  => 'billing',
            ],
            [
                'key'   => 'settings',
                'label' => 'Configuración',
                'route' => '#',
                'icon'  => 'settings',
            ],
        ];

        if ($user && $user->role === 'internal') {
            $navItems[] = [
                'key'   => 'admin',
                'label' => 'Admin',
                'route' => '#',
                'icon'  => 'admin',
            ];
        }

        $status = $this->statusService->execute($user->id);

        return view('livewire.layout.admin-sidebar', [
            'items'    => $navItems,
            'userName' => $user?->name ?? 'Usuario',
            'userPlan' => $user?->isInternal()
                ? 'Equipo Parcia'
                : 'Plan ' . ($status->plan?->label() ?? 'Gratuito'),
        ]);
    }
}
