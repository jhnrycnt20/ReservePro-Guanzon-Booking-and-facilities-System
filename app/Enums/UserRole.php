<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case FrontDesk = 'front_desk';
    case Security = 'security';
    case Guest = 'guest';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::FrontDesk => 'Front Desk',
            self::Security => 'Security',
            self::Guest => 'Guest',
        };
    }
}
