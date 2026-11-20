<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GuanzonBrandedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $emailSubject,
        public string $heading,
        public array $introLines,
        public ?string $actionText,
        public ?string $actionUrl,
        public array $outroLines = [],
    ) {
    }

    public function build(): static
    {
        return $this->subject($this->emailSubject)
            ->view('emails.guanzon')
            ->with([
                'heading' => $this->heading,
                'introLines' => $this->introLines,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
                'outroLines' => $this->outroLines,
                // Public URL only — no CID embed (avoids Gmail attachment chips).
                'logoUrl' => $this->publicLogoUrl(),
                'resortName' => 'Guanzon Resort',
                'appName' => config('app.name', 'ReservePro'),
            ]);
    }

    private function publicLogoUrl(): ?string
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl === '' || Str::contains($appUrl, ['127.0.0.1', 'localhost', '::1'])) {
            return null;
        }

        foreach (['images/guanzon_logo_green.png', 'images/guanzon_navbar.png'] as $relative) {
            if (is_file(public_path($relative))) {
                return $appUrl.'/'.$relative;
            }
        }

        return null;
    }
}
