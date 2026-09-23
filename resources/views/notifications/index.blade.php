@extends('layouts.dashboard')

@section('title', 'Notifications')
@section('theme', optional(auth()->user()->role)->slug ?? 'guest')
@section('role_label', ucfirst(str_replace('_', ' ', optional(auth()->user()->role)->slug ?? 'User')))
@section('page_title', 'Notifications')
@section('sidebar')
    @php $slug = optional(auth()->user()->role)->slug; @endphp
    @if($slug === 'admin')
        @include('partials.sidebar-admin')
    @elseif($slug === 'front_desk')
        @include('partials.sidebar-front-desk')
    @elseif($slug === 'security')
        @include('partials.sidebar-security')
    @else
        @include('partials.sidebar-guest')
    @endif
@endsection

@section('content')
<div class="rp-card">
    <div class="d-flex justify-content-between mb-3">
        <h2 class="h5 mb-0">All notifications</h2>
        <form method="POST" action="{{ route('notifications.read_all') }}">
            @csrf
            <button class="btn btn-sm btn-rp-soft">Mark all read</button>
        </form>
    </div>
    <div class="list-group list-group-flush">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.open', $notification->id) }}" class="list-group-item list-group-item-action px-0 {{ $notification->read_at ? '' : 'fw-semibold' }}">
                <div class="d-flex justify-content-between gap-3 align-items-start">
                    <div>
                        <div>{{ $notification->data['message'] ?? $notification->data['title'] ?? 'Notification' }}</div>
                        <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="small text-nowrap">Open <i class="bi bi-chevron-right"></i></span>
                </div>
            </a>
        @empty
            <div class="text-muted">No notifications yet.</div>
        @endforelse
    </div>
    @if(method_exists($notifications, 'links'))
        <div class="mt-3">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
