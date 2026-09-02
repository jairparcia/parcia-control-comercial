<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class AdminLayoutSidebar extends Component
{
    public bool $collapsed = false;
    public string $active  = 'plans';

    public function toggle(): void
    {
        $this->collapsed = ! $this->collapsed;
    }

    public function render()
    {
        $user = auth()->user();

        $name = $user?->name ?? 'User';

        $navItems = [
            [
                'key'      => 'plans',
                'label'    => 'Plans',
                'route'    => route('admin.plans'),
                'icon'     => 'plans',
                'isActive' => $this->active === 'plans',
            ],
            [
                'key'      => 'subscriptions',
                'label'    => 'Subscriptions',
                'route'    => route('admin.subscriptions'),
                'icon'     => 'subscriptions',
                'isActive' => $this->active === 'subscriptions',
            ],
            [
                'key'      => 'customers',
                'label'    => 'Customers',
                'route'    => route('admin.customers'),
                'icon'     => 'customers',
                'isActive' => $this->active === 'customers',
            ],
            [
                'key'      => 'invoices',
                'label'    => 'Invoices',
                'route'    => route('admin.invoices'),
                'icon'     => 'invoices',
                'isActive' => $this->active === 'invoices',
            ],
        ];

        return view('livewire.admin.admin-layout-sidebar', [
            'items'        => $navItems,
            'userName'     => $name,
            'userEmail'    => $user?->email ?? '',
            'userInitials' => strtoupper(mb_substr($name, 0, 2)),
        ]);
    }
}
