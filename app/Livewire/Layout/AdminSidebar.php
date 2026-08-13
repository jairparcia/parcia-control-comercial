<?php

namespace App\Livewire\Layout;

use Livewire\Component;

class AdminSidebar extends Component
{
    public bool $collapsed = false;
    public string $active = 'dashboard';

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
                'route' => '#',
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

        return view('livewire.layout.admin-sidebar', [
            'items'    => $navItems,
            'userName' => $user?->name ?? 'Usuario',
            'userPlan' => $user?->role === 'internal' ? 'Equipo Parcia' : 'Plan Starter',
        ]);
    }
}
