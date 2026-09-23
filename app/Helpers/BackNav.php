<?php

namespace App\Helpers;

class BackNav
{
    /**
     * Resolve a "Back to X" label + URL from a `from` source key (e.g. 'offers', 'home').
     * Falls back to Accommodations when the source is missing or unrecognized.
     */
    public static function resolve(?string $from): array
    {
        $sources = [
            'offers' => ['label' => 'Back to Offers', 'url' => route('offers')],
            'home' => ['label' => 'Back to The Resort', 'url' => url('/')],
            'gallery' => ['label' => 'Back to Gallery', 'url' => route('gallery')],
            'contact' => ['label' => 'Back to Contact', 'url' => route('contact')],
        ];

        return $sources[$from] ?? ['label' => 'Back to Accommodations', 'url' => route('accommodations.browse')];
    }
}
