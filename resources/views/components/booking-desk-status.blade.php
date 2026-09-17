@props(['booking'])

@php
    $value = $booking->status instanceof \BackedEnum ? $booking->status->value : (string) $booking->status;
    $awaitingStay = in_array($value, ['pending', 'approved'], true);

    if ($awaitingStay && ! $booking->hasMetDepositRequirement()) {
        $label = 'Reserved';
        $badgeStatus = 'reserved';
    } elseif ($awaitingStay && $booking->hasMetDepositRequirement()) {
        $label = 'Booked';
        $badgeStatus = 'approved';
    } else {
        $label = match ($value) {
            'checked_in' => 'Checked in',
            default => str_replace('_', ' ', ucwords($value, '_')),
        };
        $badgeStatus = $value;
    }
@endphp

<x-status-badge :status="$badgeStatus" :label="$label" />
