<?php

namespace App\Enums;

enum AccommodationStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    /** Statuses admins may set manually in forms. */
    public static function manualValues(): array
    {
        return [
            self::Available->value,
            self::Maintenance->value,
            self::Reserved->value,
        ];
    }

    public static function manualLabels(): array
    {
        return [
            self::Available->value => 'Available',
            self::Maintenance->value => 'Maintenance',
            self::Reserved->value => 'Reserved',
        ];
    }
}
