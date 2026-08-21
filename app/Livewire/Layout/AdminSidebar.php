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
                'key'      => 'dashboard',
                'label'    => 'Dashboard',
                'route'    => route('dashboard'),
                'icon'     => 'home',
                'isActive' => $this->active === 'dashboard',
            ],
            [
                'key'      => 'creators',
                'label'    => 'Mis Creadores',
                'route'    => '#',
                'icon'     => 'creators',
                'isActive' => $this->active === 'creators',
            ],
            [
                'key'      => 'billing',
                'label'    => 'Facturación',
                'route'    => route('billing'),
                'icon'     => 'billing',
                'isActive' => $this->active === 'billing',
            ],
            [
                'key'      => 'settings',
                'label'    => 'Configuración',
                'route'    => '#',
                'icon'     => 'settings',
                'isActive' => $this->active === 'settings',
            ],
        ];

        $status = $this->statusService->execute($user->id);
        $name   = $user?->name ?? 'Usuario';

        return view('livewire.layout.admin-sidebar', [
            'items'         => $navItems,
            'adminRoute'    => ($user && $user->role === 'internal') ? route('admin.plans') : null,
            'isAdminActive' => $this->active === 'admin',
            'userName'      => $name,
            'userInitials'  => strtoupper(mb_substr($name, 0, 2)),
            'userPlan'      => $user?->isInternal()
                ? 'Equipo Parcia'
                : 'Plan ' . ($status->plan?->label() ?? 'Gratuito'),
        ]);
    }
}
