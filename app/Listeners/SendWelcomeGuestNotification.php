<?php

namespace App\Listeners;

use App\Notifications\WelcomeGuestNotification;
use Illuminate\Auth\Events\Registered;
use Throwable;

class SendWelcomeGuestNotification
{
    public function handle(Registered $event): void
    {
        try {
            $event->user->notify(new WelcomeGuestNotification);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
