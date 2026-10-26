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
    $filterId = uniqid('rp-filter-');
@endphp
<div class="rp-filter-bar {{ $embedded ? 'rp-filter-bar--embedded' : '' }}">
<div class="rp-filter-compact {{ $embedded ? 'rp-filter-compact--embedded' : '' }} {{ $activeCount > 0 ? 'is-active' : '' }}" data-rp-filter-compact>
    <button
        type="button"
        class="rp-filter-compact-toggle"
        data-rp-filter-toggle
        aria-expanded="false"
        aria-controls="{{ $filterId }}"
        title="Filters"
    >
        <i class="bi bi-funnel" aria-hidden="true"></i>
        <span class="rp-filter-compact-label">Filters</span>
        @if($activeCount > 0)
            <span class="rp-filter-compact-badge">{{ $activeCount }}</span>
        @endif
        <i class="bi bi-chevron-down rp-filter-compact-caret" aria-hidden="true"></i>
    </button>

    <div id="{{ $filterId }}" class="rp-filter-compact-panel" data-rp-filter-panel hidden>
        <div class="rp-filter-compact-header">
            <div class="rp-filter-compact-title">Filter results</div>
            <button type="button" class="rp-filter-compact-close" data-rp-filter-close aria-label="Close filters"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <form method="GET" class="rp-filter-compact-form">
            @foreach($filters as $filter)
                <div class="rp-filter-compact-field">
                    <label class="form-label" for="{{ $filterId }}-{{ $loop->index }}">{{ $filter['label'] }}</label>
                    <select id="{{ $filterId }}-{{ $loop->index }}" name="{{ $filter['name'] }}" class="form-select">
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
                    <label class="form-label" for="{{ $filterId }}-date">{{ $dateLabel ?? 'Date' }}</label>
                    <input id="{{ $filterId }}-date" type="date" name="{{ $dateName }}" value="{{ request($dateName) }}" class="form-control">
                </div>
            @endisset

            <div class="rp-filter-compact-field">
                <label class="form-label" for="{{ $filterId }}-search">Search</label>
                <div class="rp-filter-compact-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input
                        id="{{ $filterId }}-search"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        class="form-control"
                        placeholder="{{ $searchPlaceholder }}"
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="rp-filter-compact-actions">
                @if($activeCount > 0)
                    <a href="{{ $clearUrl }}" class="rp-filter-compact-clear">Clear all</a>
                @endif
                <button type="submit" class="btn btn-rp-primary">Apply filters</button>
            </div>
        </form>
    </div>
</div>
</div>
