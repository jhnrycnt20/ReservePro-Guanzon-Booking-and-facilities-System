<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Demo actor accounts (seeded on every deploy)
    |--------------------------------------------------------------------------
    |
    | Password for all accounts: password
    |
    */
    'password' => 'password',

    'accounts' => [
        [
            'name' => 'Demo Guest',
            'email' => 'guest@reservepro.test',
            'phone' => '09000000004',
            'role' => 'guest',
            'label' => 'Guest',
            'guest_address' => 'Demo Address, Guanzon',
        ],
        [
            'name' => 'System Admin',
            'email' => 'admin@reservepro.test',
            'phone' => '09000000001',
            'role' => 'admin',
            'label' => 'Admin',
        ],
        [
            'name' => 'Front Desk Staff',
            'email' => 'frontdesk@reservepro.test',
            'phone' => '09000000002',
            'role' => 'front_desk',
            'label' => 'Front Desk',
        ],
        [
            'name' => 'Security Guard',
            'email' => 'security@reservepro.test',
            'phone' => '09000000003',
            'role' => 'security',
            'label' => 'Security',
        ],
    ],
];
