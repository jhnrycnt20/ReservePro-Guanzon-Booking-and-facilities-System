@php
    $feedbackBooking = (isset($booking) && $booking instanceof \App\Models\Booking && $booking->exists)
        ? $booking
        : null;
@endphp
<div class="modal fade rp-feedback-compose-modal" id="rpWriteFeedbackModal" tabindex="-1" aria-labelledby="rpWriteFeedbackModalTitle" aria-hidden="true"
     data-rp-auto-open="{{ $errors->feedback->any() || session('open_feedback_modal') ? '1' : '0' }}"
     data-rp-auto-booking-action="{{ $feedbackBooking ? route('guest.feedback.store_booking', $feedbackBooking) : '' }}"
     data-rp-auto-booking-label="{{ $feedbackBooking ? $feedbackBooking->booking_number.' — '.($feedbackBooking->accommodation->name ?? 'Stay') : '' }}">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="rpWriteFeedbackModalTitle">Write Feedback</h2>
                </div>
            </div>
            <form method="POST" action="{{ route('guest.feedback.store') }}" class="rp-feedback-compose-form" data-rp-general-action="{{ route('guest.feedback.store') }}">
                @csrf
                <input type="hidden" name="_modal" value="feedback">
                <input type="hidden" name="scope" value="{{ old('scope', old('accommodation_id') ? 'room' : 'resort') }}" data-rp-feedback-scope>
                <div class="modal-body">
                    <div class="rp-feedback-compose-booking-summary" data-rp-feedback-booking-summary hidden></div>
                    <div class="rp-feedback-compose-field rp-feedback-compose-rating">
                        <label class="form-label">Your rating</label>
                        @include('partials.star-rating-input', [
                            'ratingValue' => old('rating', 5),
                            'ratingId' => 'rpComposeFeedbackStars',
                        ])
                        @error('rating', 'feedback')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="rp-feedback-compose-field" data-rp-feedback-accommodation-field>
                        <label class="form-label" for="rpComposeFeedbackAccommodation">Accommodation stayed in <span class="text-muted fw-normal">(optional)</span></label>
                        <select id="rpComposeFeedbackAccommodation" name="accommodation_id" class="form-select" data-rp-feedback-accommodation>
                            <option value="">No specific accommodation · Resort-wide feedback</option>
                            @foreach($feedbackStays as $stay)
                                <option value="{{ $stay->accommodation_id }}" @selected((string) old('accommodation_id') === (string) $stay->accommodation_id)>
                                    {{ $stay->accommodation->name }}@if(filled($stay->accommodation->number)) · Unit {{ $stay->accommodation->number }}@endif · Stayed {{ $stay->check_out_date->format('M j, Y') }}
                                </option>
                            @endforeach
                        </select>
                        @if($feedbackStays->isEmpty())
                            <div class="form-text">Completed stays will appear here.</div>
                        @endif
                        @error('accommodation_id', 'feedback')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="rp-feedback-compose-field">
                        <label class="form-label" for="rpComposeFeedbackComment">Your review</label>
                        <textarea id="rpComposeFeedbackComment" name="comment" class="form-control @error('comment', 'feedback') is-invalid @enderror" rows="4" maxlength="2000" placeholder="Tell us about your experience" required>{{ old('comment') }}</textarea>
                        @error('comment', 'feedback')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="rp-feedback-compose-submit">Submit feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('rpWriteFeedbackModal');
    const form = modal?.querySelector('.rp-feedback-compose-form');
    const scope = modal?.querySelector('[data-rp-feedback-scope]');
    const accommodation = modal?.querySelector('[data-rp-feedback-accommodation]');
    const accommodationField = modal?.querySelector('[data-rp-feedback-accommodation-field]');
    const bookingSummary = modal?.querySelector('[data-rp-feedback-booking-summary]');
    const comment = modal?.querySelector('#rpComposeFeedbackComment');
    if (!modal || !form || !scope || !accommodation || !accommodationField || !bookingSummary || !comment) return;

    const syncFeedbackScope = () => {
        scope.value = accommodation.value ? 'room' : 'resort';
    };
    const useBooking = (action, label) => {
        form.action = action;
        accommodationField.hidden = true;
        bookingSummary.textContent = label;
        bookingSummary.hidden = false;
        comment.required = false;
    };
    const useGeneralFeedback = () => {
        form.action = form.dataset.rpGeneralAction;
        accommodationField.hidden = false;
        bookingSummary.hidden = true;
        bookingSummary.textContent = '';
        comment.required = true;
        syncFeedbackScope();
    };

    accommodation.addEventListener('change', syncFeedbackScope);
    syncFeedbackScope();

    modal.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const action = trigger?.dataset.rpFeedbackBookingAction;
        if (action) useBooking(action, trigger.dataset.rpFeedbackBookingLabel || '');
        else useGeneralFeedback();
    });
    modal.addEventListener('hidden.bs.modal', useGeneralFeedback);

    if (modal.dataset.rpAutoOpen === '1') {
        const action = modal.dataset.rpAutoBookingAction;
        if (action) useBooking(action, modal.dataset.rpAutoBookingLabel || '');
        bootstrap.Modal.getOrCreateInstance(modal).show();
    }
});
</script>
@endpush
