<?php

namespace App\Enums;

enum IncidentType: string
{
    case Incident = 'incident';
    case BrokenAmenity = 'broken_amenity';
    case Complaint = 'complaint';
    case Maintenance = 'maintenance';
}
