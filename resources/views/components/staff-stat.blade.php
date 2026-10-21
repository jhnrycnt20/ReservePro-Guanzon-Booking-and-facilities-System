@props(['label', 'value', 'icon', 'hint' => null])

<div class="rp-staff-stat">
    <div class="rp-staff-stat-top">
        <span class="rp-staff-stat-label">{{ $label }}</span>
        <span class="rp-staff-stat-icon" aria-hidden="true"><i class="bi {{ $icon }}"></i></span>
    </div>
    <div class="rp-staff-stat-value">{{ $value }}</div>
    @if($hint)
        <div class="rp-staff-stat-hint">{{ $hint }}</div>
    @endif
</div>
