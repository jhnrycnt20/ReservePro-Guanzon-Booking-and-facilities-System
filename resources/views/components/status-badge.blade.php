@props(['status'])

@php
    $map = [
        'pending' => 'badge-status-pending',
        'approved' => 'badge-status-approved',
        'rejected' => 'badge-status-rejected',
        'cancelled' => 'badge-status-cancelled',
        'checked_in' => 'badge-status-checked-in',
        'checked-in' => 'badge-status-checked-in',
        'checked_out' => 'badge-status-checked-out',
        'checked-out' => 'badge-status-checked-out',
        'verified' => 'badge-status-verified',
        'invalid' => 'badge-status-invalid',
        'in_progress' => 'badge-status-in-progress',
        'in-progress' => 'badge-status-in-progress',
        'resolved' => 'badge-status-resolved',
        'closed' => 'badge-status-closed',
        'available' => 'badge-status-approved',
        'reserved' => 'badge-status-pending',
        'occupied' => 'badge-status-checked-in',
        'maintenance' => 'badge-status-rejected',
        'inactive' => 'badge-status-cancelled',
        'unpaid' => 'badge-status-pending',
        'partially_paid' => 'badge-status-in-progress',
        'fully_paid' => 'badge-status-approved',
        'refunded' => 'badge-status-cancelled',
    ];
    $value = is_object($status) && property_exists($status, 'value') ? $status->value : (string) $status;
    $class = $map[$value] ?? 'badge-status-cancelled';
    $label = str_replace('_', ' ', ucwords($value, '_'));
@endphp

<span {{ $attributes->merge(['class' => 'badge rp-status-badge '.$class]) }}>{{ $label }}</span>
