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
<div class="container py-4">
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
            <input type="date" name="check_in" value="{{ request('check_in') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Check-out</label>
            <input type="date" name="check_out" value="{{ request('check_out') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <button class="btn btn-rp-primary w-100">Check Availability</button>
        </div>
    </form>
</div>

<div class="row g-4">
    @forelse($accommodations ?? [] as $item)
        <div class="col-md-6 col-xl-4">
            <div class="rp-card accommodation-card h-100">
                <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
                <div class="d-flex justify-content-between align-items-start mt-3">
                    <div>
                        <h2 class="h5 mb-1">{{ $item->name }}</h2>
                        <div class="text-muted small">{{ $item->type->name ?? $item->accommodationType->name ?? 'Accommodation' }} · Cap {{ $item->capacity }}</div>
                    </div>
                    <x-status-badge :status="$item->status" />
                </div>
                <p class="mt-2 mb-3 text-muted">{{ \Illuminate\Support\Str::limit($item->description, 100) }}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <strong>₱{{ number_format($item->rate, 2) }}/night</strong>
                    <a href="{{ route('accommodations.show', $item) }}" class="btn btn-sm btn-rp-primary">View & Book</a>
                </div>
            </div>
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
