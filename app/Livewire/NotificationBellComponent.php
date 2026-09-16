<?php

namespace App\Livewire;

use App\Application\Notifications\NotificationService;
use App\Http\Presenters\NotificationPresenter;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class NotificationBellComponent extends Component
{
    #[Reactive]
    public bool $collapsed = false;

    public bool $open = false;

    private NotificationService   $notificationService;
    private NotificationPresenter $presenter;

    public function boot(NotificationService $notificationService, NotificationPresenter $presenter): void
    {
        $this->notificationService = $notificationService;
        $this->presenter           = $presenter;
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markAllAsRead(): void
    {
        $this->notificationService->markAllAsRead(auth()->id());
    }

    public function markAsRead(int $id): void
    {
        $this->notificationService->markAsRead($id, auth()->id());
    }

    public function render()
    {
        return view('livewire.notification-bell-component', [
            'notifications' => $this->open
                ? $this->presenter->presentAll($this->notificationService->listForUser(auth()->id()))
                : [],
            'unreadCount' => $this->notificationService->unreadCount(auth()->id()),
        ]);
    }
}
