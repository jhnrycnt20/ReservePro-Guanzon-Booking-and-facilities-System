<?php

namespace App\Notifications;

use App\Mail\GuanzonBrandedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WelcomeGuestNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        $mailer = (string) config('mail.default', 'smtp');
        $smtpHost = (string) config('mail.mailers.smtp.host', '');
        $canMail = in_array($mailer, ['log', 'array'], true)
            || ($mailer === 'smtp' && $smtpHost !== '' && ! in_array($smtpHost, ['mailpit', 'localhost', '127.0.0.1'], true) && filled(config('mail.mailers.smtp.username')));

        if ($canMail) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): GuanzonBrandedMail
    {
        $name = $notifiable->name ?? 'Guest';
        $app = config('app.name', 'ReservePro');

        return (new GuanzonBrandedMail(
            emailSubject: 'Welcome to Guanzon Resort',
            heading: "Welcome, {$name}!",
            introLines: [
                "Your guest account on {$app} for Guanzon Resort is ready.",
                'Browse facilities, reserve rooms and cottages, and manage your bookings anytime.',
            ],
            actionText: 'Go to My Bookings',
            actionUrl: url('/guest/bookings'),
            outroLines: [
                'If you did not create this account, please ignore this email or contact the resort.',
            ],
        ))->to($notifiable->email);
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
