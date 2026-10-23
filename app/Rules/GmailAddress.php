<?php

namespace App\Rules;

use App\Services\GmailExistenceChecker;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Accepts only Gmail addresses (@gmail.com / @googlemail.com)
 * with username rules that match Google's common constraints.
 * Optionally verifies the mailbox exists via SMTP probe.
 */
class GmailAddress implements ValidationRule
{
    public function __construct(
        private readonly bool $checkExists = false,
    ) {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim((string) $value));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fail('Enter a valid Gmail address (example@gmail.com).');

            return;
        }

        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            $fail('Enter a valid Gmail address (example@gmail.com).');

            return;
        }

        [$local, $domain] = $parts;

        if (! in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $fail('Only a Gmail address (@gmail.com) is accepted.');

            return;
        }

        // Gmail delivers user+tag@gmail.com to user@gmail.com — validate the base username.
        $base = explode('+', $local, 2)[0];

        if ($base === '' || strlen($base) < 6 || strlen($base) > 30) {
            $fail('Gmail usernames must be 6–30 characters before @gmail.com.');

            return;
        }

        if (! preg_match('/^[a-z0-9.]+$/', $base)) {
            $fail('Gmail usernames may only use letters, numbers, and periods.');

            return;
        }

        if ($base[0] === '.' || str_ends_with($base, '.') || str_contains($base, '..')) {
            $fail('Enter a valid Gmail username (no leading, trailing, or double periods).');

            return;
        }

        if (! $this->checkExists) {
            return;
        }

        $exists = app(GmailExistenceChecker::class)->check($email);

        if ($exists === false) {
            $fail('This Gmail does not exist. Please enter a real Gmail address.');
        }
    }
}
