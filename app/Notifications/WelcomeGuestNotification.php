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
        $channels = ['database'];

        // Skip SMTP on hosts without real mail config (Render defaults to unreachable smtp/mailpit).
        $mailer = (string) config('mail.default', 'smtp');
        $smtpHost = (string) config('mail.mailers.smtp.host', '');
        $canMail = in_array($mailer, ['log', 'array'], true)
            || ($mailer === 'smtp' && $smtpHost !== '' && ! in_array($smtpHost, ['mailpit', 'localhost', '127.0.0.1'], true) && filled(config('mail.mailers.smtp.username')));

        if ($canMail) {
            $channels[] = 'mail';
        }

        return $channels;
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
