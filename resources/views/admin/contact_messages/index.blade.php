@extends('layouts.dashboard')

@section('title', 'Contact Messages')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Contact Messages')
@section('page_subtitle', 'Inquiries from the public contact form')
@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
@include('partials.list-filters', [
    'filters' => [
        [
            'name' => 'read',
            'label' => 'Read status',
            'empty' => 'All',
            'value' => request('read'),
            'options' => [
                'unread' => 'Unread',
                'read' => 'Read',
            ],
        ],
    ],
    'searchPlaceholder' => 'Name, email, subject, or message',
    'clearUrl' => route('admin.contact-messages.index'),
])
<div class="rp-card">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Phone</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $item)
                    <tr class="{{ $item->read_at ? '' : 'fw-semibold' }}" data-message-row="{{ $item->id }}">
                        <td>
                            <div>{{ $item->name }}</div>
                            <div class="small text-muted">{{ $item->email }}</div>
                        </td>
                        <td>{{ $item->subject ?: '—' }}</td>
                        <td>{{ $item->phone ?: '—' }}</td>
                        <td>{{ $item->created_at?->format('M d, Y g:i A') }}</td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary"
                                data-bs-toggle="modal"
                                data-bs-target="#contactMessageViewModal"
                                data-payload="{{ base64_encode(json_encode([
                                    'id' => $item->id,
                                    'name' => $item->name,
                                    'email' => $item->email,
                                    'phone' => $item->phone,
                                    'subject' => $item->subject,
                                    'message' => $item->message,
                                    'received' => $item->created_at?->format('M d, Y g:i A'),
                                    'mark_read_url' => route('admin.contact-messages.show', $item),
                                    'delete_url' => route('admin.contact-messages.destroy', $item),
                                ], JSON_UNESCAPED_UNICODE)) }}"
                            >View</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-muted">No contact messages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($messages, 'links')) {{ $messages->withQueryString()->links() }} @endif
</div>

<div class="modal fade" id="contactMessageViewModal" tabindex="-1" aria-labelledby="contactMessageViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="contactMessageViewModalLabel">Contact message</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <dl class="rp-contact-view-meta">
                    <div class="rp-contact-view-meta-item rp-contact-view-meta-item--wide">
                        <dt>From</dt>
                        <dd>
                            <span class="rp-contact-view-name" id="contactViewName">—</span>
                            <a href="#" id="contactViewEmail" class="rp-contact-view-email">—</a>
                        </dd>
                    </div>
                    <div class="rp-contact-view-meta-item">
                        <dt>Phone</dt>
                        <dd id="contactViewPhone">—</dd>
                    </div>
                    <div class="rp-contact-view-meta-item">
                        <dt>Received</dt>
                        <dd id="contactViewReceived">—</dd>
                    </div>
                </dl>

                <div class="rp-contact-view-section">
                    <div class="rp-contact-view-label">Subject</div>
                    <div class="rp-contact-view-subject" id="contactViewSubject">—</div>
                </div>

                <div class="rp-contact-view-section mb-0">
                    <div class="rp-contact-view-label">Message</div>
                    <div id="contactViewMessage" class="rp-contact-message-body">—</div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <form method="POST" id="contactMessageDeleteForm" data-rp-confirm="Delete this message?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
                <div class="d-flex gap-2">
                    <a href="#" id="contactViewReply" class="btn btn-rp-primary">Reply by email</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('contactMessageViewModal')?.addEventListener('show.bs.modal', (event) => {
    const button = event.relatedTarget;
    if (!button) return;

    let payload = {};
    try {
        const raw = button.dataset.payload || '';
        payload = JSON.parse(raw ? atob(raw) : '{}');
    } catch (error) {
        payload = {};
    }

    const name = payload.name || '—';
    const email = payload.email || '';
    const phone = payload.phone || '—';
    const subject = payload.subject || '—';
    const message = payload.message || '—';
    const received = payload.received || '—';
    const deleteUrl = payload.delete_url || '';
    const markReadUrl = payload.mark_read_url || '';
    const messageId = payload.id || '';

    document.getElementById('contactViewName').textContent = name;
    document.getElementById('contactViewPhone').textContent = phone || '—';
    document.getElementById('contactViewReceived').textContent = received;
    document.getElementById('contactViewMessage').textContent = message || '—';

    const emailLink = document.getElementById('contactViewEmail');
    if (email) {
        emailLink.textContent = email;
        emailLink.href = `mailto:${email}`;
        emailLink.classList.remove('d-none');
    } else {
        emailLink.textContent = '';
        emailLink.removeAttribute('href');
        emailLink.classList.add('d-none');
    }

    const subjectEl = document.getElementById('contactViewSubject');
    if (subjectEl) {
        const cleanSubject = (payload.subject || '').trim();
        subjectEl.textContent = cleanSubject !== '' ? cleanSubject : '(No subject)';
    }

    const replyLink = document.getElementById('contactViewReply');
    const replySubject = encodeURIComponent(`Re: ${subject && subject !== '—' ? subject : 'Your inquiry'}`);
    if (email) {
        replyLink.href = `mailto:${email}?subject=${replySubject}`;
        replyLink.classList.remove('disabled');
    } else {
        replyLink.href = '#';
        replyLink.classList.add('disabled');
    }

    const deleteForm = document.getElementById('contactMessageDeleteForm');
    if (deleteForm) {
        deleteForm.action = deleteUrl;
    }

    if (markReadUrl) {
        fetch(markReadUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).then(() => {
            const row = document.querySelector(`[data-message-row="${messageId}"]`);
            row?.classList.remove('fw-semibold');
        }).catch(() => {});
    }
});
</script>
@endpush
