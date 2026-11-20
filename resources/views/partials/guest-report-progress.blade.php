@php
    $statusValue = $report->status instanceof \BackedEnum
        ? $report->status->value
        : (string) $report->status;

    $isInvalid = $statusValue === 'invalid';
    $steps = [
        ['key' => 'submitted', 'label' => 'Submitted'],
        ['key' => 'review', 'label' => 'Under review'],
        ['key' => 'processing', 'label' => 'Being processed'],
        ['key' => 'resolved', 'label' => 'Resolved'],
    ];

    $current = match ($statusValue) {
        'pending' => 0,
        'verified' => 1,
        'in_progress' => 2,
        'resolved', 'closed' => 3,
        'invalid' => -1,
        default => 0,
    };

    $stageLabel = match ($statusValue) {
        'pending' => 'Submitted — waiting for review',
        'verified' => 'Under review by staff',
        'in_progress' => 'Being processed by the front desk',
        'resolved', 'closed' => 'Resolved',
        'invalid' => 'Marked invalid',
        default => str_replace('_', ' ', ucwords($statusValue, '_')),
    };
    $accommodation = $report->booking?->accommodation;
    $headingLabel = $accommodation
        ? ($accommodation->name ?: 'Accommodation').(filled($accommodation->number) ? ' · Unit '.$accommodation->number : '')
        : $stageLabel;
@endphp

<div class="rp-guest-report-progress {{ $isInvalid ? 'is-invalid-state' : '' }}">
    <div class="rp-guest-report-card-head">
        <span class="rp-guest-report-card-icon" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
        <div>
            <div class="rp-guest-report-progress-stage">{{ $headingLabel }}</div>
        </div>
    </div>

    @if($isInvalid)
        <div class="rp-guest-report-progress-note">This report was reviewed and marked invalid.</div>
    @else
        <ol class="rp-guest-report-steps" style="--rp-progress-current: {{ max($current, 0) }};">
            @foreach($steps as $index => $step)
                @php
                    $state = $index < $current ? 'is-done' : ($index === $current ? 'is-current' : 'is-todo');
                @endphp
                <li class="rp-guest-report-step {{ $state }}">
                    <span class="rp-guest-report-step-dot" aria-hidden="true">
                        @if($state === 'is-done')
                            <i class="bi bi-check"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </span>
                    <span class="rp-guest-report-step-label">{{ $step['label'] }}</span>
                </li>
            @endforeach
        </ol>
    @endif
</div>
