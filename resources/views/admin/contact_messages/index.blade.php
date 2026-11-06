@extends('layouts.dashboard')

@section('title', 'Contact Messages')
@section('theme', 'admin')
@section('role_label', 'Administrator')
@section('page_title', 'Contact Messages')
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
    'searchPlaceholder' => 'Name, Email, Subject, or Message',
    'clearUrl' => route('admin.contact-messages.index'),
])
<div class="rp-card rp-admin-list-card">
    <div class="table-responsive">
        <table class="table align-middle rp-admin-list-table rp-admin-list-table--contact">
            <thead>
                <tr>
                    <th scope="col" style="width: 24%;">From</th>
                    <th scope="col" style="width: 30%;">Subject</th>
                    <th scope="col" style="width: 16%;">Phone</th>
                    <th scope="col" style="width: 18%;">Received</th>
                    <th scope="col" class="rp-admin-list-actions-heading" style="width: 12%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $item)
                    <tr>
                        <td class="rp-admin-list-wrap">
                            <div class="rp-admin-list-primary">{{ $item->name }}</div>
                            <div class="rp-admin-list-secondary">{{ $item->email }}</div>
                        </td>
                        <td class="rp-admin-list-wrap">{{ $item->subject ?: '—' }}</td>
                        <td class="rp-admin-list-mono">{{ $item->phone ?: '—' }}</td>
                        <td class="rp-admin-list-mono">{{ $item->created_at?->format('M d, Y g:i A') }}</td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-rp-soft"
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rp-contact-view-modal">
            <div class="modal-header rp-contact-view-header">
                <div class="rp-contact-view-header-copy">
                    <span class="rp-contact-view-kicker">Inbox</span>
                    <h2 class="modal-title" id="contactMessageViewModalLabel">Contact message</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body rp-contact-view-body">
                <div class="rp-contact-view-intro">
                    <p class="rp-contact-view-eyebrow">Subject</p>
                    <h3 class="rp-contact-view-subject" id="contactViewSubject">—</h3>
                    <div class="rp-contact-view-received">
                        <i class="bi bi-clock" aria-hidden="true"></i>
                        <span>Received <span id="contactViewReceived">—</span></span>
                    </div>
                </div>

                <div class="rp-contact-view-sender-card">
                    <div class="rp-contact-view-avatar" id="contactViewAvatar" aria-hidden="true">?</div>
                    <div class="rp-contact-view-sender-copy">
                        <p class="rp-contact-view-eyebrow">From</p>
                        <div class="rp-contact-view-name" id="contactViewName">—</div>
                        <a href="#" id="contactViewEmail" class="rp-contact-view-email">—</a>
                    </div>
                    <div class="rp-contact-view-phone-row">
                        <span class="rp-contact-view-eyebrow">Phone</span>
                        <span id="contactViewPhone">—</span>
                    </div>
                </div>

                <section class="rp-contact-view-content">
                    <p class="rp-contact-view-eyebrow">Message</p>
                    <div id="contactViewMessage" class="rp-contact-message-body">—</div>
                </section>
            </div>
            <div class="modal-footer rp-contact-view-footer">
                <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Close</button>
                <a href="#" id="contactViewReply" class="btn btn-rp-primary">
                    <i class="bi bi-reply" aria-hidden="true"></i>
                    Reply by email
                </a>
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
        payload = JSON.parse(raw ? new TextDecoder().decode(Uint8Array.from(atob(raw), (char) => char.charCodeAt(0))) : '{}');
    } catch (error) {
        payload = {};
    }

    const name = payload.name || '—';
    const email = payload.email || '';
    const phone = payload.phone || '—';
    const subject = payload.subject || '—';
    const message = payload.message || '—';
    const received = payload.received || '—';
    const markReadUrl = payload.mark_read_url || '';

    document.getElementById('contactViewName').textContent = name;
    document.getElementById('contactViewPhone').textContent = phone || '—';
    document.getElementById('contactViewReceived').textContent = received;
    document.getElementById('contactViewMessage').textContent = message || '—';

    const avatar = document.getElementById('contactViewAvatar');
    if (avatar) {
        const initial = (name || '?').trim().charAt(0).toUpperCase() || '?';
        avatar.textContent = initial;
    }

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

    if (markReadUrl) {
        fetch(markReadUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        }).catch(() => {});
    }
});
</script>
@endpush
