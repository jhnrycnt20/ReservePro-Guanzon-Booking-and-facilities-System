document.addEventListener('DOMContentLoaded', () => {
    const publicNav = document.querySelector('.rp-public-nav');

    if (publicNav) {
        const scrollThreshold = 120;
        let navIsScrolled = false;

        const updateNavOnScroll = () => {
            const shouldBeScrolled = window.scrollY > scrollThreshold;
            if (shouldBeScrolled !== navIsScrolled) {
                navIsScrolled = shouldBeScrolled;
                publicNav.classList.toggle('rp-nav-scrolled', navIsScrolled);
            }
        };

        updateNavOnScroll();
        window.addEventListener('scroll', updateNavOnScroll, { passive: true });
    }

    const scrollTopBtn = document.getElementById('rpScrollTop');
    scrollTopBtn?.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    const planSlider = document.getElementById('rpPlanSlider');

    if (planSlider) {
        let isDown = false;
        let startX = 0;
        let startScroll = 0;
        let velocity = 0;
        let lastX = 0;
        let lastT = 0;
        let momentumId = null;

        const stopMomentum = () => {
            if (momentumId) cancelAnimationFrame(momentumId);
            momentumId = null;
        };

        const runMomentum = () => {
            if (Math.abs(velocity) < 0.5) {
                stopMomentum();
                return;
            }
            planSlider.scrollLeft -= velocity;
            velocity *= 0.94;
            momentumId = requestAnimationFrame(runMomentum);
        };

        planSlider.addEventListener('pointerdown', (e) => {
            isDown = true;
            stopMomentum();
            planSlider.classList.add('is-dragging');
            startX = e.clientX;
            startScroll = planSlider.scrollLeft;
            lastX = e.clientX;
            lastT = performance.now();
            velocity = 0;
            planSlider.setPointerCapture(e.pointerId);
        });

        planSlider.addEventListener('pointermove', (e) => {
            if (!isDown) return;
            const dx = e.clientX - startX;
            planSlider.scrollLeft = startScroll - dx;

            const now = performance.now();
            const dt = now - lastT || 16;
            velocity = (e.clientX - lastX) / dt * 16;
            lastX = e.clientX;
            lastT = now;
        });

        const endDrag = (e) => {
            if (!isDown) return;
            isDown = false;
            planSlider.classList.remove('is-dragging');
            if (e?.pointerId !== undefined && planSlider.hasPointerCapture?.(e.pointerId)) {
                planSlider.releasePointerCapture(e.pointerId);
            }
            runMomentum();
        };

        planSlider.addEventListener('pointerup', endDrag);
        planSlider.addEventListener('pointercancel', endDrag);
        planSlider.addEventListener('pointerleave', endDrag);

        planSlider.querySelectorAll('a, img').forEach((el) => {
            el.addEventListener('dragstart', (e) => e.preventDefault());
        });
    }

    const sidebar = document.getElementById('rpSidebar');
    const toggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');

    const closeSidebar = () => {
        sidebar?.classList.remove('open');
        backdrop?.classList.remove('show');
    };

    toggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('open');
        backdrop?.classList.toggle('show');
    });

    backdrop?.addEventListener('click', closeSidebar);

    const navMenuBtn = document.getElementById('rpNavMenuBtn');
    const navOverlay = document.getElementById('rpNavOverlay');

    const closeNavOverlay = () => {
        navMenuBtn?.classList.remove('is-open');
        navOverlay?.classList.remove('is-open');
        navMenuBtn?.setAttribute('aria-expanded', 'false');
    };

    navMenuBtn?.addEventListener('click', () => {
        const isOpen = navMenuBtn.classList.toggle('is-open');
        navOverlay?.classList.toggle('is-open', isOpen);
        navMenuBtn.setAttribute('aria-expanded', String(isOpen));
    });

    navOverlay?.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeNavOverlay);
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeNavOverlay();
    });

    document.addEventListener('click', (e) => {
        if (!navOverlay?.classList.contains('is-open')) return;
        if (navOverlay.contains(e.target) || navMenuBtn?.contains(e.target)) return;
        closeNavOverlay();
    });

    const toastEl = document.getElementById('rpToast');
    if (toastEl && window.bootstrap) {
        new bootstrap.Toast(toastEl).show();
    }

    const checkIn = document.querySelector('[data-calc-check-in]');
    const checkOut = document.querySelector('[data-calc-check-out]');
    const rate = document.querySelector('[data-calc-rate]');
    const total = document.querySelector('[data-calc-total]');

    const calcTotal = () => {
        if (!checkIn || !checkOut || !rate || !total) return;
        const start = new Date(checkIn.value);
        const end = new Date(checkOut.value);
        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end <= start) {
            total.textContent = '—';
            return;
        }
        const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        const amount = nights * parseFloat(rate.dataset.calcRate || rate.value || '0');
        total.textContent = `₱${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (estimate)`;
    };

    checkIn?.addEventListener('change', calcTotal);
    checkOut?.addEventListener('change', calcTotal);

    const pad = (n) => String(n).padStart(2, '0');
    const toYmd = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const addDays = (ymd, days) => {
        const date = new Date(`${ymd}T00:00:00`);
        date.setDate(date.getDate() + days);
        return toYmd(date);
    };
    const todayYmd = toYmd(new Date());

    const bindStayDates = (checkInEl, checkOutEl) => {
        checkInEl.min = todayYmd;
        if (checkInEl.value && checkInEl.value < todayYmd) {
            checkInEl.value = '';
        }

        const syncCheckOutMin = () => {
            const minOut = checkInEl.value ? addDays(checkInEl.value, 1) : addDays(todayYmd, 1);
            checkOutEl.min = minOut;
            if (checkOutEl.value && checkOutEl.value < minOut) {
                checkOutEl.value = minOut;
            }
            calcTotal();
        };

        syncCheckOutMin();
        checkInEl.addEventListener('change', syncCheckOutMin);
    };

    document.querySelectorAll('[data-stay-check-in]').forEach((checkInEl) => {
        const form = checkInEl.closest('form');
        const checkOutEl = form?.querySelector('[data-stay-check-out]');
        if (checkOutEl) {
            bindStayDates(checkInEl, checkOutEl);
        }
    });

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    togglePassword?.addEventListener('click', () => {
        if (!passwordInput) return;
        const show = passwordInput.type === 'password';
        passwordInput.type = show ? 'text' : 'password';
        togglePassword.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        togglePasswordIcon?.classList.toggle('bi-eye', !show);
        togglePasswordIcon?.classList.toggle('bi-eye-slash', show);
    });

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    }

    let deferredInstallPrompt = null;
    const installBtn = document.getElementById('pwaInstallBtn');

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installBtn?.classList.remove('d-none');
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        installBtn.classList.add('d-none');
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        installBtn?.classList.add('d-none');
    });

    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    const iosInstallHelp = document.getElementById('iosInstallHelp');

    if (isIos && !isStandalone) {
        iosInstallHelp?.classList.remove('d-none');
    }
});
