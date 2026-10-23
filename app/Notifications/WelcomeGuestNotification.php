<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeGuestNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $name = $notifiable->name ?? 'Guest';
        $app = config('app.name', 'ReservePro');

        return (new MailMessage)
            ->subject("Welcome to {$app}!")
            ->greeting("Hello, {$name}!")
            ->line("Your guest account on {$app} has been created successfully.")
            ->line('You can now browse facilities, make reservations, and manage your bookings anytime.')
            ->action('Go to My Bookings', url('/guest/bookings'))
            ->line('If you did not create this account, please ignore this email or contact the resort.');
    }

    public function toArray(object $notifiable): array
    {
        $app = config('app.name', 'ReservePro');

        return [
            'title' => "Welcome to {$app}",
            'message' => 'Your guest account was created successfully. You can start booking facilities anytime.',
            'type' => 'account',
        ];
    }
}
