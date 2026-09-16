<?php

namespace App\Http\Presenters;

use App\Domain\Notifications\Results\NotificationResult;
use Carbon\Carbon;

class NotificationPresenter
{
    /**
     * @param NotificationResult[] $notifications
     * @return NotificationViewModel[]
     */
    public function presentAll(array $notifications): array
    {
        return array_map(fn (NotificationResult $n) => $this->present($n), $notifications);
    }

    private function present(NotificationResult $n): NotificationViewModel
    {
        return new NotificationViewModel(
            id:        $n->id,
            title:     $n->title,
            body:      $n->body,
            isRead:    $n->isRead,
            createdAt: Carbon::instance($n->createdAt)->locale(app()->getLocale())->isoFormat('D MMM YYYY'),
        );
    }
}
