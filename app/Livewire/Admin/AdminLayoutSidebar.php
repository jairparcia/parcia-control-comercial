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

        $name = $user?->name ?? __('admin.user');

        $navItems = [
            [
                'key'      => 'plans',
                'label'    => __('admin.plans'),
                'route'    => route('admin.plans'),
                'icon'     => 'plans',
                'isActive' => $this->active === 'plans',
            ],
            [
                'key'      => 'subscriptions',
                'label'    => __('admin.subscriptions'),
                'route'    => route('admin.subscriptions'),
                'icon'     => 'subscriptions',
                'isActive' => $this->active === 'subscriptions',
            ],
            [
                'key'      => 'customers',
                'label'    => __('admin.customers'),
                'route'    => route('admin.customers'),
                'icon'     => 'customers',
                'isActive' => $this->active === 'customers',
            ],
            [
                'key'      => 'transactions',
                'label'    => __('admin.transactions'),
                'route'    => route('admin.transactions'),
                'icon'     => 'transactions',
                'isActive' => $this->active === 'transactions',
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
