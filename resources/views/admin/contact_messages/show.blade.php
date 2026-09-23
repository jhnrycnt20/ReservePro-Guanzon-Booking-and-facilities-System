@extends('layouts.dashboard')

@section('title', 'Contact Message')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Contact Message')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-outline-secondary">&larr; Back</a>
</div>

<div class="rp-card">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="text-muted small">From</div>
            <div class="fw-semibold">{{ $message->name }}</div>
            <div><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Phone</div>
            <div>{{ $message->phone ?: '—' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">Received</div>
            <div>{{ $message->created_at?->format('M d, Y g:i A') }}</div>
        </div>
    </div>

    <div class="mb-3">
        <div class="text-muted small">Subject</div>
        <div class="fw-semibold">{{ $message->subject ?: '—' }}</div>
    </div>

    <div class="mb-4">
        <div class="text-muted small">Message</div>
        <div style="white-space: pre-wrap;">{{ $message->message }}</div>
    </div>

    <div class="d-flex gap-2">
        <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject ?: 'Your inquiry') }}" class="btn btn-rp-primary">Reply by email</a>
        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
