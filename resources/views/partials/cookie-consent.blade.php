<div class="rp-cookie-banner" id="rpCookieBanner" hidden>
    <div class="rp-cookie-banner-inner">
        <p class="rp-cookie-banner-text">We use cookies to improve your experience and analyze website traffic. Read our <a href="{{ route('legal.privacy') }}#cookies">Cookie Policy</a>.</p>
        <div class="rp-cookie-banner-actions">
            <button type="button" class="rp-cookie-btn rp-cookie-btn-outline" data-rp-cookie-manage>Manage Preferences</button>
            <button type="button" class="rp-cookie-btn rp-cookie-btn-outline" data-rp-cookie-reject>Reject Non-Essential</button>
            <button type="button" class="rp-cookie-btn rp-cookie-btn-primary" data-rp-cookie-accept>Accept All</button>
        </div>
    </div>
</div>

<div class="modal fade" id="rpCookiePrefsModal" tabindex="-1" aria-labelledby="rpCookiePrefsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rp-cookie-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 mb-0" id="rpCookiePrefsModalLabel">Cookie Preferences</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="rp-cookie-pref-row">
                    <div>
                        <div class="rp-cookie-pref-title">Necessary</div>
                        <div class="rp-cookie-pref-desc">Required for login, booking, and security. Always on.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" checked disabled aria-label="Necessary cookies (always on)">
                    </div>
                </div>
                <div class="rp-cookie-pref-row">
                    <div>
                        <div class="rp-cookie-pref-title">Analytics</div>
                        <div class="rp-cookie-pref-desc">Helps us understand how visitors use the site.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rpCookieAnalytics">
                    </div>
                </div>
                <div class="rp-cookie-pref-row">
                    <div>
                        <div class="rp-cookie-pref-title">Marketing</div>
                        <div class="rp-cookie-pref-desc">Used to show relevant offers and measure promotions.</div>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="rpCookieMarketing">
                    </div>
                </div>
                <p class="rp-cookie-pref-note">Read the full <a href="{{ route('legal.privacy') }}#cookies">Cookie Policy</a> for details on what each category covers.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="rp-cookie-btn rp-cookie-btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="rp-cookie-btn rp-cookie-btn-primary" data-rp-cookie-save>Save Preferences</button>
            </div>
        </div>
    </div>
</div>
