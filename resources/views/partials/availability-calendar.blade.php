<div class="modal fade" id="rpAvailabilityModal" tabindex="-1" aria-labelledby="rpAvailabilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-availability-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h2 class="modal-title h5 mb-1" id="rpAvailabilityModalLabel">Select your dates</h2>
                    <p class="text-muted small mb-0">Red dates are already booked. Choose available dates for check-in and check-out.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <div class="rp-availability-legend mb-3">
                    <span><i class="rp-legend-dot rp-legend-available"></i> Available</span>
                    <span><i class="rp-legend-dot rp-legend-occupied"></i> Occupied</span>
                    <span><i class="rp-legend-dot rp-legend-selected"></i> Selected</span>
                </div>
                <div class="rp-availability-calendar" data-rp-availability-calendar>
                    <div class="rp-availability-calendar-header">
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-cal-prev aria-label="Previous month">&lsaquo;</button>
                        <div class="fw-semibold" data-rp-cal-title></div>
                        <button type="button" class="btn btn-sm btn-rp-soft" data-rp-cal-next aria-label="Next month">&rsaquo;</button>
                    </div>
                    <div class="rp-availability-weekdays">
                        <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                    </div>
                    <div class="rp-availability-days" data-rp-cal-days></div>
                    <div class="small text-muted mt-3" data-rp-cal-selection>Pick a check-in date, then a check-out date.</div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-rp-soft" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-rp-primary" data-rp-cal-apply disabled>Apply dates</button>
            </div>
        </div>
    </div>
</div>
