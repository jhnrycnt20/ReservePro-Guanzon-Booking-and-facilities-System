@extends('layouts.dashboard')

@section('title', 'Contact Message')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Contact Message')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@php
    $initial = strtoupper(mb_substr(trim((string) $message->name) ?: '?', 0, 1));
@endphp
<div class="mb-3">
    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-rp-soft">&larr; Back to messages</a>
</div>

<div class="rp-card rp-contact-page-card">
    <div class="rp-contact-view-intro">
        <p class="rp-contact-view-eyebrow">Subject</p>
        <h1 class="rp-contact-view-subject">{{ $message->subject ?: '(No subject)' }}</h1>
        <div class="rp-contact-view-received">
            <i class="bi bi-clock" aria-hidden="true"></i>
            <span>Received {{ $message->created_at?->format('M d, Y g:i A') }}</span>
        </div>
    </div>

    <div class="rp-contact-view-sender-card mt-3">
        <div class="rp-contact-view-avatar" aria-hidden="true">{{ $initial }}</div>
        <div class="rp-contact-view-sender-copy">
            <p class="rp-contact-view-eyebrow">From</p>
            <div class="rp-contact-view-name">{{ $message->name }}</div>
            <a href="mailto:{{ $message->email }}" class="rp-contact-view-email">{{ $message->email }}</a>
        </div>
        <div class="rp-contact-view-phone-row">
            <span class="rp-contact-view-eyebrow">Phone</span>
            <span>{{ $message->phone ?: '—' }}</span>
        </div>
    </div>

    <section class="rp-contact-view-content px-0 pt-3">
        <p class="rp-contact-view-eyebrow">Message</p>
        <div class="rp-contact-message-body">{{ $message->message }}</div>
    </section>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="mailto:{{ $message->email }}?subject={{ urlencode('Re: '.($message->subject ?: 'Your inquiry')) }}" class="btn btn-rp-primary">
            <i class="bi bi-reply" aria-hidden="true"></i> Reply by email
        </a>
        <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" onsubmit="return confirm('Delete this message?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
