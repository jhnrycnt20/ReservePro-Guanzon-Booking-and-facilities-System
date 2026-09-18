<div class="modal fade" id="rpPromoDatetimeModal" tabindex="-1" aria-labelledby="rpPromoDatetimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rp-promo-dt-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="rpPromoDatetimeModalLabel">Select date &amp; time</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="rp-promo-dt-layout">
                    <div class="rp-promo-dt-calendar" data-rp-promo-dt-calendar>
                        <div class="rp-promo-dt-cal-header">
                            <button type="button" class="btn btn-sm btn-rp-soft" data-rp-promo-dt-prev aria-label="Previous month">&lsaquo;</button>
                            <div class="fw-semibold" data-rp-promo-dt-title>Month</div>
                            <button type="button" class="btn btn-sm btn-rp-soft" data-rp-promo-dt-next aria-label="Next month">&rsaquo;</button>
                        </div>
                        <div class="rp-promo-dt-weekdays">
                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                        </div>
                        <div class="rp-promo-dt-days" data-rp-promo-dt-days></div>
                    </div>
                    <div class="rp-promo-dt-time">
                        <div class="rp-promo-dt-time-cols">
                            <div class="rp-promo-dt-time-col">
                                <div class="rp-promo-dt-time-label">Hour</div>
                                <div class="rp-promo-dt-time-list" data-rp-promo-dt-hour></div>
                            </div>
                            <div class="rp-promo-dt-time-col">
                                <div class="rp-promo-dt-time-label">Minute</div>
                                <div class="rp-promo-dt-time-list" data-rp-promo-dt-minute></div>
                            </div>
                            <div class="rp-promo-dt-time-col">
                                <div class="rp-promo-dt-time-label">AM/PM</div>
                                <div class="rp-promo-dt-time-list" data-rp-promo-dt-ampm></div>
                            </div>
                        </div>
                        <div class="rp-promo-dt-preview" data-rp-promo-dt-preview>—</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-rp-soft" data-rp-promo-dt-clear>Clear</button>
                    <button type="button" class="btn btn-rp-soft" data-rp-promo-dt-today>Today</button>
                </div>
                <button type="button" class="btn btn-rp-primary" data-rp-promo-dt-ok>Okay</button>
            </div>
        </div>
    </div>
</div>
