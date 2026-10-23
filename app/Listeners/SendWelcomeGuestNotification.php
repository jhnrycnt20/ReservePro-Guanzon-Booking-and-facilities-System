<?php

namespace App\Listeners;

use App\Notifications\WelcomeGuestNotification;
use Illuminate\Auth\Events\Registered;

class SendWelcomeGuestNotification
{
    public function handle(Registered $event): void
    {
        $event->user->notify(new WelcomeGuestNotification);
    }
}
