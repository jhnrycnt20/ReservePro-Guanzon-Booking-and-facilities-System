<?php

return [

    'secret_key' => env('PAYMONGO_SECRET_KEY'),

    'public_key' => env('PAYMONGO_PUBLIC_KEY'),

    'webhook_secret' => env('PAYMONGO_WEBHOOK_SECRET'),

    /*
    | When true and secret_key is set, guests use PayMongo GCash checkout instead of manual QR/proof.
    */
    'enabled' => env('PAYMONGO_ENABLED', false),

];
