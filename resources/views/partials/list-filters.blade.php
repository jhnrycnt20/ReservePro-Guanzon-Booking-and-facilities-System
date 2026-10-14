{{--
  Shared compact filter control (icon + dropdown panel).
  Expected variables:
  - $filters: array of select configs [
        ['name' => 'status', 'label' => 'Status', 'options' => ['pending' => 'Pending'], 'value' => request('status'), 'empty' => 'All']
     ]
  - $searchPlaceholder (optional)
  - $dateName / $dateLabel (optional single date field)
  - $clearUrl (optional; defaults to current route without query)
  - $embedded (optional)
--}}
@php
    $filters = $filters ?? [];
    $searchPlaceholder = $searchPlaceholder ?? 'Search…';
    $embedded = !empty($embedded);
    $clearUrl = $clearUrl ?? url()->current();
    $activeCount = 0;
    foreach ($filters as $filter) {
        if (filled($filter['value'] ?? null)) {
            $activeCount++;
        }
    }
    if (!empty($dateName) && filled(request($dateName))) {
        $activeCount++;
    }
    if (filled(request('q'))) {
        $activeCount++;
    }
@endphp
<div class="rp-filter-bar {{ $embedded ? 'rp-filter-bar--embedded' : '' }}">
<div class="rp-filter-compact {{ $embedded ? 'rp-filter-compact--embedded' : '' }} {{ $activeCount > 0 ? 'is-active' : '' }}" data-rp-filter-compact>
    <button
        type="button"
        class="rp-filter-compact-toggle"
        data-rp-filter-toggle
        aria-expanded="false"
        aria-haspopup="true"
        title="Filters"
    >
        <i class="bi bi-funnel" aria-hidden="true"></i>
        <span class="rp-filter-compact-label">Filter</span>
        @if($activeCount > 0)
            <span class="rp-filter-compact-badge">{{ $activeCount }}</span>
        @endif
        <i class="bi bi-chevron-down rp-filter-compact-caret" aria-hidden="true"></i>
    </button>

    <div class="rp-filter-compact-panel" data-rp-filter-panel hidden>
        <form method="GET" class="rp-filter-compact-form" data-rp-live-filter>
            @foreach($filters as $filter)
                <div class="rp-filter-compact-field">
                    <label class="form-label">{{ $filter['label'] }}</label>
                    <select name="{{ $filter['name'] }}" class="form-select" data-rp-live-filter-change>
                        @if(array_key_exists('empty', $filter))
                            <option value="">{{ $filter['empty'] }}</option>
                        @endif
                        @foreach($filter['options'] as $value => $label)
                            <option value="{{ $value }}" @selected((string) ($filter['value'] ?? '') === (string) $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            @isset($dateName)
                <div class="rp-filter-compact-field">
                    <label class="form-label">{{ $dateLabel ?? 'Date' }}</label>
                    <input type="date" name="{{ $dateName }}" value="{{ request($dateName) }}" class="form-control" data-rp-live-filter-change>
                </div>
            @endisset

            <div class="rp-filter-compact-field">
                <label class="form-label">Search</label>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    class="form-control"
                    placeholder="{{ $searchPlaceholder }}"
                    data-rp-live-filter-q
                    autocomplete="off"
                >
            </div>

            <div class="rp-filter-compact-actions">
                <button type="submit" class="btn btn-rp-primary">Apply</button>
                @if($activeCount > 0)
                    <a href="{{ $clearUrl }}" class="btn btn-rp-soft">Clear</a>
                @endif
            </div>
        </form>
    </div>
</div>
</div>
