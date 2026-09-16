{{--
  Shared list filter bar.
  Expected variables:
  - $filters: array of select configs [
        ['name' => 'status', 'label' => 'Status', 'options' => ['pending' => 'Pending'], 'value' => request('status'), 'empty' => 'All']
     ]
  - $searchPlaceholder (optional)
  - $dateName / $dateLabel (optional single date field)
  - $clearUrl (optional; defaults to current route without query)
--}}
@php
    $filters = $filters ?? [];
    $searchPlaceholder = $searchPlaceholder ?? 'Search…';
    $clearUrl = $clearUrl ?? url()->current();
    $hasActive = collect(request()->except('page'))->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
    $colSearch = max(3, 12 - (count($filters) * 2) - (!empty($dateName) ? 2 : 0) - 3);
    $embedded = !empty($embedded);
@endphp
<div class="rp-card {{ $embedded ? 'mb-0 rp-filter-with-action__card' : 'mb-3' }}">
    <form method="GET" class="row g-2 align-items-end" data-rp-live-filter>
        @foreach($filters as $filter)
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label">{{ $dateLabel ?? 'Date' }}</label>
                <input type="date" name="{{ $dateName }}" value="{{ request($dateName) }}" class="form-control" data-rp-live-filter-change>
            </div>
        @endisset

        <div class="col-md-{{ min(6, $colSearch) }}">
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
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-rp-primary flex-grow-1">Filter</button>
            @if($hasActive)
                <a href="{{ $clearUrl }}" class="btn btn-rp-soft">Clear</a>
            @endif
        </div>
    </form>
</div>
