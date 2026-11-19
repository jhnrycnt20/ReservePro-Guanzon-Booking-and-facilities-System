<?php

namespace App\Support;

class PasswordResetLinkBoard
{
    public static function enabled(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        return in_array(config('mail.default'), ['log', 'array'], true);
    }

    public static function store(string $email, string $url): void
    {
        if (! self::enabled()) {
            return;
        }

        $links = cache('dev_password_reset_links', []);
        array_unshift($links, [
            'email' => $email,
            'url' => $url,
            'at' => now()->toDateTimeString(),
        ]);

        cache()->put('dev_password_reset_links', array_slice($links, 0, 20), now()->addDay());
    }

    public static function all(): array
    {
        return cache('dev_password_reset_links', []);
    }
}
