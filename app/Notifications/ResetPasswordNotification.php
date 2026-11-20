<?php

namespace App\Notifications;

use App\Mail\GuanzonBrandedMail;
use Illuminate\Auth\Notifications\ResetPassword;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): GuanzonBrandedMail
    {
        $name = $notifiable->name ?? 'Guest';
        $app = config('app.name', 'ReservePro');
        $url = $this->resetUrl($notifiable);
        $expire = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new GuanzonBrandedMail(
            emailSubject: 'Reset your Guanzon Resort password',
            heading: "Hello, {$name}!",
            introLines: [
                'We received a request to reset the password for your Guanzon Resort account.',
                "This reset link expires in {$expire} minutes.",
            ],
            actionText: 'Reset Password',
            actionUrl: $url,
            outroLines: [
                'If you did not request a password reset, you can ignore this email.',
            ],
        ))->to($notifiable->email);
    }
}
