<?php

namespace App\Notifications\Concerns;

trait StoresDatabaseMessage
{
    protected function payload(string $title, string $message, array $extra = []): array
    {
        return array_merge([
            'title' => $title,
            'message' => $message,
        ], $extra);
    }
}
