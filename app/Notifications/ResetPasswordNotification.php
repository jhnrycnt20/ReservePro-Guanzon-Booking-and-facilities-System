<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $name = $notifiable->name ?? 'Guest';
        $app = config('app.name', 'ReservePro');
        $url = $this->resetUrl($notifiable);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject("Reset your {$app} password")
            ->greeting("Hello, {$name}!")
            ->line('We received a request to reset the password for your Guanzon Resort account.')
            ->action('Reset Password', $url)
            ->line("This link expires in {$expire} minutes.")
            ->line('If you did not request a password reset, you can ignore this email.');
    }
}
