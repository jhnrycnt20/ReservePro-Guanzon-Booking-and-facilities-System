@php
    $rpTrackerSteps = [
        'dates' => ['icon' => 'bi-calendar3', 'label' => 'Check-in &amp;<br>Check-out Date'],
        'guest' => ['icon' => 'bi-person', 'label' => 'Guest<br>Information'],
        'confirmation' => ['icon' => 'bi-check-lg', 'label' => 'Booking<br>Confirmation'],
    ];
    $rpActiveStep = $activeStep ?? 'dates';
@endphp
<div class="rp-booking-tracker">
    @foreach ($rpTrackerSteps as $rpStepKey => $rpStep)
        <div class="rp-booking-tracker-step @if($rpActiveStep === $rpStepKey) is-active @endif">
            <div class="rp-booking-tracker-icon"><i class="bi {{ $rpStep['icon'] }}"></i></div>
            <div class="rp-booking-tracker-label">{!! $rpStep['label'] !!}</div>
        </div>
    @endforeach
</div>
