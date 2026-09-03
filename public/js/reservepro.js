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

    const galleryBannerBg = document.querySelector('.rp-gallery-banner-bg');

    if (galleryBannerBg) {
        const parallaxRatio = 0.12;
        const maxOffset = 24;
        let ticking = false;

        const updateParallax = () => {
            const rect = galleryBannerBg.parentElement.getBoundingClientRect();
            const offset = Math.max(-maxOffset, Math.min(maxOffset, -rect.top * parallaxRatio));
            galleryBannerBg.style.transform = `scale(1.15) translateY(${offset}px)`;
            ticking = false;
        };

        updateParallax();
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });
    }

    const galleryItems = document.querySelectorAll('#rpGalleryGrid .rp-gallery-item');
    const revealTargets = document.querySelectorAll('#rpGalleryGrid .rp-gallery-item, .rp-story-image');

    if (revealTargets.length) {
        if ('IntersectionObserver' in window) {
            galleryItems.forEach((item, index) => {
                item.style.transitionDelay = `${(index % 4) * 0.08}s`;
            });

            const revealObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

            revealTargets.forEach((item) => revealObserver.observe(item));
        } else {
            revealTargets.forEach((item) => item.classList.add('is-visible'));
        }
    }

    const offersContent = document.getElementById('rpOffersContent');
    const offersNextBtn = document.getElementById('rpOffersNextBtn');

    if (offersContent && offersNextBtn) {
        const offerRows = Array.from(offersContent.querySelectorAll('.rp-offer-row'));
        const pageSize = 2;
        const totalPages = Math.ceil(offerRows.length / pageSize);
        let currentPage = 0;

        const renderOffersPage = () => {
            offerRows.forEach((row, index) => {
                const page = Math.floor(index / pageSize);
                const isVisible = page === currentPage;
                row.classList.remove('is-visible', 'rp-offer-row--first', 'rp-offer-row--last');
                row.style.display = isVisible ? 'flex' : 'none';
            });

            const visibleRows = offerRows.filter((_, index) => Math.floor(index / pageSize) === currentPage);
            visibleRows[0]?.classList.add('rp-offer-row--first');
            visibleRows[visibleRows.length - 1]?.classList.add('rp-offer-row--last');

            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    visibleRows.forEach((row) => row.classList.add('is-visible'));
                });
            });
        };

        offersNextBtn.addEventListener('click', () => {
            currentPage = (currentPage + 1) % totalPages;
            renderOffersPage();
        });

        if (totalPages <= 1) {
            offersNextBtn.style.display = 'none';
        }

        renderOffersPage();
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

    window.addEventListener('scroll', () => {
        if (navOverlay?.classList.contains('is-open') && publicNav?.classList.contains('rp-nav-scrolled')) {
            closeNavOverlay();
        }
    }, { passive: true });

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

    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const emailInput = document.getElementById('email');
            const demoPasswordInput = document.getElementById('password');
            if (!emailInput || !demoPasswordInput) return;

            emailInput.value = button.dataset.demoEmail || '';
            demoPasswordInput.value = button.dataset.demoPassword || '';
            emailInput.dispatchEvent(new Event('input', { bubbles: true }));
            demoPasswordInput.dispatchEvent(new Event('input', { bubbles: true }));
            emailInput.focus();
        });
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

    initAvailabilityCalendar();
});

function initAvailabilityCalendar() {
    const modalEl = document.getElementById('rpAvailabilityModal');
    const form = document.querySelector('[data-rp-availability-form]');
    const calendarRoot = document.querySelector('[data-rp-availability-calendar]');

    if (!modalEl || !form || !calendarRoot || !window.bootstrap) {
        return;
    }

    const occupiedUrl = form.dataset.occupiedUrl;
    const checkInInput = form.querySelector('[data-stay-check-in]');
    const checkOutInput = form.querySelector('[data-stay-check-out]');
    const titleEl = calendarRoot.querySelector('[data-rp-cal-title]');
    const daysEl = calendarRoot.querySelector('[data-rp-cal-days]');
    const selectionEl = calendarRoot.querySelector('[data-rp-cal-selection]');
    const applyBtn = modalEl.querySelector('[data-rp-cal-apply]');
    const prevBtn = calendarRoot.querySelector('[data-rp-cal-prev]');
    const nextBtn = calendarRoot.querySelector('[data-rp-cal-next]');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    const pad = (n) => String(n).padStart(2, '0');
    const toYmd = (date) => `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    const parseYmd = (ymd) => new Date(`${ymd}T00:00:00`);
    const todayYmd = toYmd(new Date());
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    let viewYear = new Date().getFullYear();
    let viewMonth = new Date().getMonth() + 1;
    let occupiedSet = new Set();
    let pendingCheckIn = checkInInput?.value || '';
    let pendingCheckOut = checkOutInput?.value || '';
    let selectingCheckout = false;

    const updateApplyState = () => {
        const valid = pendingCheckIn && pendingCheckOut && pendingCheckOut > pendingCheckIn;
        applyBtn.disabled = !valid;
    };

    const formatSelection = () => {
        if (!pendingCheckIn) {
            return 'Pick a check-in date, then a check-out date.';
        }
        if (!pendingCheckOut) {
            return `Check-in: ${pendingCheckIn}. Now pick a check-out date.`;
        }
        return `Check-in: ${pendingCheckIn} · Check-out: ${pendingCheckOut}`;
    };

    const isPast = (ymd) => ymd < todayYmd;
    const isOccupied = (ymd) => occupiedSet.has(ymd);

    const rangeHasOccupied = (startYmd, endYmd) => {
        let cursor = parseYmd(startYmd);
        const end = parseYmd(endYmd);
        while (cursor < end) {
            if (isOccupied(toYmd(cursor)) || isPast(toYmd(cursor))) {
                return true;
            }
            cursor.setDate(cursor.getDate() + 1);
        }
        return false;
    };

    const fetchOccupied = async (year, month) => {
        const url = new URL(occupiedUrl, window.location.origin);
        url.searchParams.set('year', String(year));
        url.searchParams.set('month', String(month));
        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
            throw new Error('Could not load availability');
        }
        const data = await response.json();
        return data.occupied || [];
    };

    const renderCalendar = () => {
        titleEl.textContent = `${monthNames[viewMonth - 1]} ${viewYear}`;
        daysEl.innerHTML = '';

        const firstDay = new Date(viewYear, viewMonth - 1, 1);
        const startOffset = firstDay.getDay();
        const daysInMonth = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = 0; i < startOffset; i += 1) {
            const spacer = document.createElement('span');
            spacer.className = 'rp-availability-day is-empty';
            spacer.setAttribute('aria-hidden', 'true');
            daysEl.appendChild(spacer);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const ymd = `${viewYear}-${pad(viewMonth)}-${pad(day)}`;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-availability-day';
            btn.textContent = String(day);
            btn.dataset.date = ymd;

            const past = isPast(ymd);
            const occupied = isOccupied(ymd);

            if (past) {
                btn.classList.add('is-past');
                btn.disabled = true;
            } else if (occupied) {
                btn.classList.add('is-occupied');
                btn.disabled = true;
                btn.title = 'Occupied';
            } else {
                btn.classList.add('is-available');
            }

            if (pendingCheckIn && ymd === pendingCheckIn) {
                btn.classList.add('is-check-in');
            }
            if (pendingCheckOut && ymd === pendingCheckOut) {
                btn.classList.add('is-check-out');
            }
            if (pendingCheckIn && pendingCheckOut && ymd > pendingCheckIn && ymd < pendingCheckOut) {
                btn.classList.add('is-in-range');
            }

            if (!btn.disabled) {
                btn.addEventListener('click', () => handleDayClick(ymd));
            }

            daysEl.appendChild(btn);
        }

        selectionEl.textContent = formatSelection();
        updateApplyState();
    };

    const handleDayClick = (ymd) => {
        if (isOccupied(ymd) || isPast(ymd)) {
            return;
        }

        if (!pendingCheckIn || selectingCheckout === false) {
            pendingCheckIn = ymd;
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        if (ymd <= pendingCheckIn) {
            pendingCheckIn = ymd;
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        if (rangeHasOccupied(pendingCheckIn, ymd)) {
            selectionEl.textContent = 'Those dates include occupied nights. Pick a different range.';
            pendingCheckOut = '';
            selectingCheckout = true;
            renderCalendar();
            return;
        }

        pendingCheckOut = ymd;
        selectingCheckout = false;
        renderCalendar();
    };

    const loadMonth = async () => {
        daysEl.classList.add('is-loading');
        try {
            const occupied = await fetchOccupied(viewYear, viewMonth);
            occupied.forEach((date) => occupiedSet.add(date));
            renderCalendar();
        } catch {
            selectionEl.textContent = 'Unable to load availability. Please try again.';
        } finally {
            daysEl.classList.remove('is-loading');
        }
    };

    const openCalendar = () => {
        pendingCheckIn = checkInInput?.value || '';
        pendingCheckOut = checkOutInput?.value || '';
        selectingCheckout = Boolean(pendingCheckIn && !pendingCheckOut);
        occupiedSet = new Set();

        const base = pendingCheckIn ? parseYmd(pendingCheckIn) : new Date();
        viewYear = base.getFullYear();
        viewMonth = base.getMonth() + 1;

        modal.show();
        loadMonth();
    };

    prevBtn?.addEventListener('click', () => {
        viewMonth -= 1;
        if (viewMonth < 1) {
            viewMonth = 12;
            viewYear -= 1;
        }
        loadMonth();
    });

    nextBtn?.addEventListener('click', () => {
        viewMonth += 1;
        if (viewMonth > 12) {
            viewMonth = 1;
            viewYear += 1;
        }
        loadMonth();
    });

    applyBtn?.addEventListener('click', () => {
        if (!pendingCheckIn || !pendingCheckOut) {
            return;
        }
        checkInInput.value = pendingCheckIn;
        checkOutInput.value = pendingCheckOut;
        checkInInput.dispatchEvent(new Event('change', { bubbles: true }));
        modal.hide();
        form.submit();
    });

    document.querySelectorAll('[data-rp-show-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openCalendar();
        });
    });

    form.querySelectorAll('[data-rp-open-calendar]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openCalendar();
        });
    });
}
