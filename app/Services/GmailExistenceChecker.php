<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

        $result = null;

        try {
            $result = $this->probeSmtp($email);
        } catch (Throwable $e) {
            Log::warning('Gmail SMTP existence check failed.', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }

        // Render free blocks outbound SMTP (25/465/587). Fall back to HTTPS probe.
        if ($result === null) {
            try {
                $result = $this->probeHttps($email);
            } catch (Throwable $e) {
                Log::warning('Gmail HTTPS existence check failed.', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $result = null;
            }
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
        foreach (array_slice($hosts, 0, 3) as $host) {
            $fp = @fsockopen($host, 25, $errno, $errstr, 3);
            if (is_resource($fp)) {
                break;
            }
        }

        if (! is_resource($fp)) {
            return null;
        }

        stream_set_timeout($fp, 5);

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

            if ($code === 550 || $code === 551 || $code === 553 || str_contains($response, '5.1.1')) {
                return false;
            }

            if ($code === 250 || $code === 251 || $code === 252 || $code === 552) {
                return true;
            }

            return null;
        } finally {
            fclose($fp);
        }
    }

    /**
     * HTTPS mailbox probe for hosts that block outbound SMTP (e.g. Render free).
     */
    private function probeHttps(string $email): ?bool
    {
        $response = Http::timeout(20)
            ->withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'Origin' => 'https://gmail.k1god.com',
                'Referer' => 'https://gmail.k1god.com/',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])
            ->asJson()
            ->post('https://gmail.k1god.com/api/v1/check', [
                'emails' => [$email],
                'mode' => 'precision',
            ]);

        if (! $response->successful()) {
            return null;
        }

        $results = $response->json('results');
        if (! is_array($results) || $results === []) {
            return null;
        }

        $match = collect($results)->first(
            fn ($row) => strtolower((string) data_get($row, 'email')) === $email
        );

        if (! is_array($match)) {
            return null;
        }

        $status = strtolower(trim((string) data_get($match, 'status', '')));

        if (in_array($status, ['unregistered', 'disabled'], true)) {
            return false;
        }

        if (in_array($status, ['live', 'verify'], true)) {
            return true;
        }

        return null;
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
