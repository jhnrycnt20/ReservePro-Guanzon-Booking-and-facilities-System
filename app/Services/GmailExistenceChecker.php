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

        $parts = explode('@', $email, 2);
        $domain = $parts[1] ?? '';
        if (! in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            return false;
        }

        $cacheKey = 'gmail-exists:v2:'.sha1($email);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Production/Render: HTTPS first (outbound SMTP ports are blocked on free plans).
        // Local: SMTP first (accurate + fast), then HTTPS fallback.
        $probes = app()->environment('production')
            ? ['https', 'smtp']
            : ['smtp', 'https'];

        $result = null;
        foreach ($probes as $probe) {
            try {
                $result = $probe === 'https'
                    ? $this->probeHttps($email)
                    : $this->probeSmtp($email);
            } catch (Throwable $e) {
                Log::warning("Gmail {$probe} existence check failed.", [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $result = null;
            }

            if ($result !== null) {
                break;
            }
        }

        if ($result !== null) {
            Cache::put($cacheKey, $result, now()->addMinutes(30));
        }

        return $result;
    }

    /**
     * True only when the mailbox is confirmed to exist.
     */
    public function exists(string $email): bool
    {
        return $this->check($email) === true;
    }

    private function probeSmtp(string $email): ?bool
    {
        $hosts = [];
        if (function_exists('getmxrr')) {
            @getmxrr('gmail.com', $hosts);
        }
        if ($hosts === []) {
            $hosts = ['gmail-smtp-in.l.google.com'];
        }

        $fp = null;
        foreach (array_slice($hosts, 0, 2) as $host) {
            $fp = @fsockopen($host, 25, $errno, $errstr, 2);
            if (is_resource($fp)) {
                break;
            }
        }

        if (! is_resource($fp)) {
            return null;
        }

        stream_set_timeout($fp, 4);

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
        $response = Http::timeout(25)
            ->withHeaders([
                'Accept' => 'application/json, text/plain, */*',
                'Origin' => 'https://gmail.k1god.com',
                'Referer' => 'https://gmail.k1god.com/',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            ])
            ->acceptJson()
            ->asJson()
            ->post('https://gmail.k1god.com/api/v1/check', [
                'emails' => [$email],
                'email' => $email,
                'mode' => 'precision-detailed',
            ]);

        if (! $response->successful()) {
            Log::warning('Gmail HTTPS probe non-success.', [
                'email' => $email,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 300),
            ]);

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
        $category = strtolower(trim((string) data_get($match, 'category', '')));

        if (
            in_array($status, ['unregistered', 'disabled'], true)
            || in_array($category, ['unregistered', 'disabled'], true)
        ) {
            return false;
        }

        if (
            in_array($status, ['live', 'verify'], true)
            || in_array($category, ['live', 'verify'], true)
        ) {
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
