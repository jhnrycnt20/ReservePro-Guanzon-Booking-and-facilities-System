<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NotificationService
{
    public function notify(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * @param  iterable<User>  $users
     */
    public function notifyMany(iterable $users, Notification $notification): void
    {
        foreach ($users as $user) {
            $this->notify($user, $notification);
        }
    }
}
