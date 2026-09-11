(function () {
    const STORAGE_KEY = 'rp_cookie_consent';

    const getStored = () => {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch {
            return null;
        }
    };

    const save = (consent) => {
        const payload = {
            necessary: true,
            analytics: Boolean(consent.analytics),
            marketing: Boolean(consent.marketing),
            timestamp: new Date().toISOString(),
        };
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
        } catch {
            // localStorage unavailable — consent choice won't persist across visits.
        }
        window.rpCookieConsent = payload;
        document.dispatchEvent(new CustomEvent('cookie-consent:changed', { detail: payload }));
        return payload;
    };

    window.rpCookieConsent = getStored() || { necessary: true, analytics: false, marketing: false };

    document.addEventListener('DOMContentLoaded', () => {
        const banner = document.getElementById('rpCookieBanner');
        const modalEl = document.getElementById('rpCookiePrefsModal');
        const analyticsToggle = document.getElementById('rpCookieAnalytics');
        const marketingToggle = document.getElementById('rpCookieMarketing');

        if (!banner) {
            return;
        }

        const hideBanner = () => {
            banner.classList.remove('is-visible');
            setTimeout(() => {
                banner.hidden = true;
            }, 300);
        };

        const showBanner = () => {
            banner.hidden = false;
            requestAnimationFrame(() => banner.classList.add('is-visible'));
        };

        if (!getStored()) {
            showBanner();
        }

        document.querySelectorAll('[data-rp-cookie-accept]').forEach((btn) => {
            btn.addEventListener('click', () => {
                save({ analytics: true, marketing: true });
                hideBanner();
            });
        });

        document.querySelectorAll('[data-rp-cookie-reject]').forEach((btn) => {
            btn.addEventListener('click', () => {
                save({ analytics: false, marketing: false });
                hideBanner();
            });
        });

        const openPreferences = () => {
            const current = getStored() || window.rpCookieConsent;
            if (analyticsToggle) analyticsToggle.checked = Boolean(current.analytics);
            if (marketingToggle) marketingToggle.checked = Boolean(current.marketing);
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
        };

        document.querySelectorAll('[data-rp-cookie-manage], [data-rp-reopen-cookie-preferences]').forEach((btn) => {
            btn.addEventListener('click', openPreferences);
        });

        document.querySelectorAll('[data-rp-cookie-save]').forEach((btn) => {
            btn.addEventListener('click', () => {
                save({
                    analytics: analyticsToggle ? analyticsToggle.checked : false,
                    marketing: marketingToggle ? marketingToggle.checked : false,
                });
                hideBanner();
                if (modalEl && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                }
            });
        });
    });
})();
