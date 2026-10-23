<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GmailExistenceChecker
{
    /**
     * @return bool|null true = exists, false = does not exist, null = could not verify
     */
    public function check(string $email): ?bool
    {
        $email = strtolower(trim($email));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $cacheKey = 'gmail-exists:'.sha1($email);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $result = $this->probeSmtp($email);
        } catch (Throwable $e) {
            Log::warning('Gmail existence check failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if ($result !== null) {
            Cache::put($cacheKey, $result, now()->addMinutes(15));
        }

        return $result;
    }

    private function probeSmtp(string $email): ?bool
    {
        $hosts = [];
        if (! getmxrr('gmail.com', $hosts) || $hosts === []) {
            $hosts = ['gmail-smtp-in.l.google.com'];
        }

        $fp = null;
        foreach ($hosts as $host) {
            $fp = @fsockopen($host, 25, $errno, $errstr, 6);
            if (is_resource($fp)) {
                break;
            }
        }

        if (! is_resource($fp)) {
            return null;
        }

        stream_set_timeout($fp, 6);

        try {
            if (! $this->expectCode($fp, 220)) {
                return null;
            }

            $this->command($fp, 'EHLO reservepro.local');
            if (! $this->expectCode($fp, 250)) {
                return null;
            }

            $this->command($fp, 'MAIL FROM:<>');
            if (! $this->expectCode($fp, 250)) {
                return null;
            }

            $this->command($fp, 'RCPT TO:<'.$email.'>');
            $response = $this->readResponse($fp);
            $code = (int) substr($response, 0, 3);

            $this->command($fp, 'QUIT');

            // Hard "no such user"
            if ($code === 550 || $code === 551 || $code === 553 || str_contains($response, '5.1.1')) {
                return false;
            }

            // Accepted, or mailbox issues that still prove the account exists
            if ($code === 250 || $code === 251 || $code === 252 || $code === 552) {
                return true;
            }

            return null;
        } finally {
            fclose($fp);
        }
    }

    private function command($fp, string $line): void
    {
        fwrite($fp, $line."\r\n");
    }

    private function readResponse($fp): string
    {
        $data = '';

        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }

            $meta = stream_get_meta_data($fp);
            if (! empty($meta['timed_out'])) {
                break;
            }
        }

        return $data;
    }

    private function expectCode($fp, int $code): bool
    {
        $response = $this->readResponse($fp);

        return str_starts_with(trim($response), (string) $code);
    }
}
