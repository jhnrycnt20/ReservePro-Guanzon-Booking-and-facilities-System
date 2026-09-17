<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * @return Collection<int, User>
     */
    public function frontDeskStaff(): Collection
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas('role', fn ($q) => $q->whereIn('slug', ['front_desk', 'admin']))
            ->get();
    }

    public function notifyFrontDesk(Notification $notification): void
    {
        $this->notifyMany($this->frontDeskStaff(), $notification);
    }
}
