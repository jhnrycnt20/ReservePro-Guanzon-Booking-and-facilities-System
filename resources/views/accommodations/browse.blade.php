@extends(auth()->check() && optional(auth()->user()->role)->slug === 'guest' ? 'layouts.dashboard' : 'layouts.public')

@section('title', 'Browse Resort')
@section('theme', 'guest')
@section('role_label', 'Guest')
@section('page_title', 'Rooms & Rates')
@section('page_subtitle', 'Check availability before you reserve')
@section('sidebar')
    @include('partials.sidebar-guest')
@endsection

@section('content')
@if(!auth()->check() || optional(auth()->user()->role)->slug !== 'guest')
<div class="container rp-public-page-top pb-4">
@endif

<div class="rp-card mb-4">
    <form method="GET" action="{{ route('accommodations.browse') }}" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="">All types</option>
                @foreach($types ?? [] as $type)
                    <option value="{{ $type->id }}" @selected(request('type') == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Check-in</label>
            <input type="date" name="check_in" value="{{ request('check_in') }}" class="form-control" min="{{ now()->toDateString() }}" data-stay-check-in>
        </div>
        <div class="col-md-3">
            <label class="form-label">Check-out</label>
            <input type="date" name="check_out" value="{{ request('check_out') }}" class="form-control" min="{{ now()->addDay()->toDateString() }}" data-stay-check-out>
        </div>
        <div class="col-md-3">
            <button class="rp-btn-check-availability">Check Availability</button>
        </div>
    </form>
</div>

<div class="row g-4">
    @forelse($accommodations ?? [] as $item)
        <div class="col-md-4">
            <a href="{{ route('accommodations.show', $item) }}" class="rp-cottage-card">
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                <div class="rp-cottage-card-body">
                    <div class="rp-cottage-title">{{ $item->name }}</div>
                    <div class="rp-cottage-subtitle">{{ $item->type->name ?? $item->accommodationType->name ?? 'Accommodation' }}</div>
                    <div class="rp-cottage-row">
                        <span>Rate</span>
                        <span>₱{{ number_format($item->rate, 0) }}</span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>Max guests</span>
                        <span>{{ $item->capacity }}</span>
                    </div>
                    <div class="rp-cottage-row">
                        <span>Status</span>
                        <span>{{ ucfirst($item->status->value ?? $item->status) }}</span>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12"><div class="rp-card text-muted">No accommodations match your search.</div></div>
    @endforelse
</div>

@if(isset($accommodations) && method_exists($accommodations, 'links'))
    <div class="mt-4">{{ $accommodations->withQueryString()->links() }}</div>
@endif

@if(!auth()->check() || optional(auth()->user()->role)->slug !== 'guest')
</div>
@endif
@endsection
