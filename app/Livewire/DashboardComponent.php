<?php

namespace App\Livewire;

use App\Http\Presenters\DashboardPresenter;
use Livewire\Component;

class DashboardComponent extends Component
{
    public function render()
    {
        return view('livewire.dashboard-component', [
            'presenter' => new DashboardPresenter(),
        ]);
    }
}
