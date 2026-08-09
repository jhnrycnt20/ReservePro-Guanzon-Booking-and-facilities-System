<?php

namespace App\Enums;

enum IncidentStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Invalid = 'invalid';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
