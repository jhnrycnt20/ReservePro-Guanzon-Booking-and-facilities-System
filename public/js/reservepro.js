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
        const pageSize = 3;
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

    document.querySelectorAll('[data-rp-history-back]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
            }
        });
    });

    document.querySelectorAll('[data-rp-print-receipt]').forEach((button) => {
        button.addEventListener('click', () => {
            const target = document.querySelector(button.dataset.rpPrintReceipt || '#rpPaymentReceiptPrint');
            if (!target) return;

            const clone = target.cloneNode(true);
            clone.querySelectorAll('.no-print').forEach((el) => el.remove());

            // Do not use noopener here — it makes window.open() return null.
            const printWindow = window.open('', '_blank', 'width=720,height=900');
            if (!printWindow) {
                window.alert('Please allow pop-ups to print the receipt.');
                return;
            }

            printWindow.document.write(`<!DOCTYPE html><html><head><title>Payment Receipt</title>
                <style>
                    @page { size: A4; margin: 16mm; }
                    html, body { margin: 0; padding: 0; height: auto; overflow: visible; }
                    body { font-family: Georgia, "Times New Roman", serif; color: #111; }
                    .rp-receipt-card { max-width: 480px; margin: 0 auto; border: 1px solid #ddd; padding: 24px; }
                    .rp-receipt-brand { font-size: 22px; font-weight: 700; }
                    .rp-receipt-meta { color: #666; font-size: 14px; }
                    .rp-receipt-row { display: flex; justify-content: space-between; gap: 16px; margin: 8px 0; }
                    hr { border: none; border-top: 1px solid #ddd; margin: 16px 0; }
                </style></head><body>${clone.outerHTML}</body></html>`);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 250);
        });
    });

    document.querySelectorAll('[data-rp-payment-form]').forEach((form) => {
        const amountInput = form.querySelector('#paymentAmount');
        const minAmount = Number(form.dataset.rpMinAmount || 0);
        const maxAmount = Number(form.dataset.rpMaxAmount || 0);

        form.querySelectorAll('[data-rp-pay-amount]').forEach((button) => {
            button.addEventListener('click', () => {
                if (amountInput) {
                    const selectedAmount = Number(button.dataset.rpPayAmount);
                    amountInput.value = Number.isFinite(selectedAmount)
                        ? selectedAmount.toFixed(2)
                        : (button.dataset.rpPayAmount || '');
                    amountInput.dispatchEvent(new Event('input', { bubbles: true }));
                    amountInput.dispatchEvent(new Event('change', { bubbles: true }));
                }

                form.querySelectorAll('[data-rp-pay-amount]').forEach((choice) => {
                    choice.classList.toggle('is-selected', choice === button);
                });
            });
        });

        form.addEventListener('submit', (event) => {
            if (!amountInput) {
                return;
            }

            const amount = Number(String(amountInput.value || '').replace(/,/g, ''));
            if (!Number.isFinite(amount)) {
                return;
            }

            if (minAmount > 0 && amount + 0.009 < minAmount) {
                event.preventDefault();
                amountInput.setCustomValidity(`Minimum payment is ₱${minAmount.toFixed(2)} (50% deposit).`);
                amountInput.reportValidity();
                return;
            }

            if (maxAmount > 0 && amount - 0.009 > maxAmount) {
                event.preventDefault();
                amountInput.setCustomValidity(`Amount cannot exceed ₱${maxAmount.toFixed(2)}.`);
                amountInput.reportValidity();
                return;
            }

            amountInput.setCustomValidity('');
        });

        amountInput?.addEventListener('input', () => {
            amountInput.setCustomValidity('');
        });
    });

    document.querySelectorAll('[data-rp-live-filter]').forEach((form) => {
        let timer = null;
        const applyFilter = () => {
            const rawAction = form.getAttribute('action') || window.location.pathname;
            const hashFromAction = rawAction.includes('#') ? rawAction.slice(rawAction.indexOf('#')) : '';
            const action = rawAction.replace(/#.*$/, '') || window.location.pathname;
            const hash = form.dataset.rpLiveFilterHash
                ? `#${form.dataset.rpLiveFilterHash.replace(/^#/, '')}`
                : hashFromAction;
            const params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach((key) => {
                if (!(params.get(key) || '').trim()) {
                    params.delete(key);
                }
            });
            const query = params.toString();
            window.location.assign(`${action}${query ? `?${query}` : ''}${hash}`);
        };

        form.querySelectorAll('[data-rp-live-filter-q]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(timer);
                const delay = input.value.trim() === '' ? 0 : 300;
                timer = setTimeout(applyFilter, delay);
            });
        });

        form.querySelectorAll('[data-rp-live-filter-change]').forEach((select) => {
            select.addEventListener('change', () => {
                clearTimeout(timer);
                applyFilter();
            });
        });
    });

    const navMenuBtn = document.getElementById('rpNavMenuBtn');
    const navOverlay = document.getElementById('rpNavOverlay');
    let navScrollY = 0;

    const closeNavOverlay = () => {
        navMenuBtn?.classList.remove('is-open');
        navOverlay?.classList.remove('is-open');
        navMenuBtn?.setAttribute('aria-expanded', 'false');
        window.scrollTo(0, navScrollY);
    };

    navMenuBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        navScrollY = window.scrollY;
        const isOpen = navMenuBtn.classList.toggle('is-open');
        navOverlay?.classList.toggle('is-open', isOpen);
        navMenuBtn.setAttribute('aria-expanded', String(isOpen));
        window.scrollTo(0, navScrollY);
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

    const termsModalEl = document.getElementById('rpTermsModal');
    const termsModalBody = document.querySelector('[data-rp-terms-modal-body]');
    let termsScrollTarget = null;

    document.querySelectorAll('[data-rp-terms-anchor]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            termsScrollTarget = trigger.getAttribute('data-rp-terms-anchor');
        });
    });

    termsModalEl?.addEventListener('shown.bs.modal', () => {
        if (!termsScrollTarget || !termsModalBody) return;
        const target = document.getElementById(termsScrollTarget);
        if (target) {
            termsModalBody.scrollTop = target.offsetTop - termsModalBody.offsetTop;
        }
        termsScrollTarget = null;
    });

    termsModalEl?.addEventListener('hidden.bs.modal', () => {
        if (termsModalBody) termsModalBody.scrollTop = 0;
    });

    const loginModalEl = document.getElementById('rpLoginModal');
    const registerModalEl = document.getElementById('rpRegisterModal');
    const authModals = [loginModalEl, registerModalEl].filter(Boolean);
    let openAuthModalCount = 0;

    authModals.forEach((modalEl) => {
        modalEl.addEventListener('show.bs.modal', () => {
            openAuthModalCount += 1;
            document.body.classList.add('rp-login-modal-open');
            authModals.forEach((other) => {
                if (other !== modalEl && other.classList.contains('show') && window.bootstrap) {
                    bootstrap.Modal.getInstance(other)?.hide();
                }
            });
        });
        modalEl.addEventListener('hidden.bs.modal', () => {
            openAuthModalCount = Math.max(0, openAuthModalCount - 1);
            if (openAuthModalCount === 0) {
                document.body.classList.remove('rp-login-modal-open');
            }
        });
    });

    const authQuery = new URLSearchParams(window.location.search);
    const wantsLoginModal = loginModalEl?.getAttribute('data-rp-autoshow') === '1' || authQuery.get('login') === '1';
    const wantsRegisterModal = registerModalEl?.getAttribute('data-rp-autoshow') === '1' || authQuery.get('signup') === '1';

    if (authQuery.has('login') || authQuery.has('signup')) {
        authQuery.delete('login');
        authQuery.delete('signup');
        const cleanQuery = authQuery.toString();
        const cleanUrl = window.location.pathname + (cleanQuery ? `?${cleanQuery}` : '') + window.location.hash;
        window.history.replaceState({}, document.title, cleanUrl);
    }

    if (wantsLoginModal && window.bootstrap) {
        new bootstrap.Modal(loginModalEl).show();
    } else if (wantsRegisterModal && window.bootstrap) {
        new bootstrap.Modal(registerModalEl).show();
    }

    const toastEl = document.getElementById('rpToast');
    if (toastEl && window.bootstrap) {
        new bootstrap.Toast(toastEl).show();
    }

    document.querySelectorAll('[data-rp-auto-dismiss]').forEach((alertEl) => {
        let dismissed = false;
        const dismiss = () => {
            if (dismissed || !alertEl.isConnected) return;
            dismissed = true;
            if (!window.bootstrap?.Alert) {
                alertEl.remove();
                return;
            }
            bootstrap.Alert.getOrCreateInstance(alertEl).close();
        };
        alertEl.querySelector('[data-bs-dismiss="alert"]')?.addEventListener('click', dismiss);
        setTimeout(dismiss, 4000);
    });

    document.querySelectorAll('[data-rp-notif]').forEach((root) => {
        const toggle = root.querySelector('[data-rp-notif-toggle]');
        const panel = root.querySelector('[data-rp-notif-panel]');
        if (!toggle || !panel) return;

        let open = false;
        let lastToggleAt = 0;

        const setOpen = (next) => {
            open = Boolean(next);
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            root.classList.toggle('is-open', open);
        };

        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            const now = Date.now();
            // Ignore accidental double-clicks that fight open/close.
            if (now - lastToggleAt < 280) return;
            lastToggleAt = now;
            setOpen(!open);
        });

        panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            if (open) setOpen(false);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) setOpen(false);
        });
    });

    const confirmModalEl = document.getElementById('rpConfirmModal');
    let pendingConfirmAction = null;

    const askConfirm = (message, onConfirm) => {
        if (!confirmModalEl || !window.bootstrap) {
            if (window.confirm(message)) onConfirm();
            return;
        }
        const messageEl = confirmModalEl.querySelector('[data-rp-confirm-message]');
        if (messageEl) messageEl.textContent = message;
        pendingConfirmAction = onConfirm;
        bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
    };

    confirmModalEl?.querySelector('[data-rp-confirm-accept]')?.addEventListener('click', () => {
        const action = pendingConfirmAction;
        pendingConfirmAction = null;
        bootstrap.Modal.getOrCreateInstance(confirmModalEl).hide();
        if (typeof action === 'function') action();
    });

    const noticeModalEl = document.getElementById('rpNoticeModal');
    const askNotice = (message, title = 'Unavailable') => {
        if (!noticeModalEl || !window.bootstrap) {
            window.alert(message);
            return;
        }
        const titleEl = noticeModalEl.querySelector('#rpNoticeModalLabel');
        const messageEl = noticeModalEl.querySelector('[data-rp-notice-message]');
        if (titleEl) titleEl.textContent = title;
        if (messageEl) messageEl.textContent = message;
        bootstrap.Modal.getOrCreateInstance(noticeModalEl).show();
    };

    document.querySelectorAll('[data-rp-blocked-click]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            askNotice(
                el.dataset.rpBlockedClick || 'This option is currently unavailable.',
                el.dataset.rpBlockedTitle || 'Unavailable'
            );
        });
    });

    document.querySelectorAll('form[data-rp-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.rpConfirmed === '1') {
                delete form.dataset.rpConfirmed;
                return;
            }
            event.preventDefault();
            askConfirm(form.dataset.rpConfirm || 'Are you sure?', () => {
                form.dataset.rpConfirmed = '1';
                form.requestSubmit();
            });
        });
    });

    document.querySelectorAll('[data-rp-confirm-click]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const form = button.closest('form');
            if (!form) return;
            if (form.dataset.rpConfirmed === '1') {
                delete form.dataset.rpConfirmed;
                return;
            }
            event.preventDefault();
            askConfirm(button.dataset.rpConfirmClick || 'Are you sure?', () => {
                form.dataset.rpConfirmed = '1';
                form.requestSubmit();
            });
        });
    });

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

    const resolveCapacity = (form) => {
        const totalEl = form.querySelector('[data-rp-guest-total]');
        if (totalEl?.dataset.rpCapacity) {
            return parseInt(totalEl.dataset.rpCapacity, 10) || 0;
        }
        const select = form.querySelector('[data-rp-capacity-select]');
        const option = select?.selectedOptions?.[0];
        return parseInt(option?.dataset.capacity || '0', 10) || 0;
    };

    const syncGuestCapacity = (form) => {
        const adultsEl = form.querySelector('[data-rp-guest-adults]');
        const childrenEl = form.querySelector('[data-rp-guest-children]');
        const totalEl = form.querySelector('[data-rp-guest-total]');
        const errorEl = form.querySelector('[data-rp-capacity-error]');
        if (!adultsEl || !childrenEl || !totalEl) return true;

        const capacity = resolveCapacity(form);
        let adults = Math.max(1, parseInt(adultsEl.value || '1', 10) || 1);
        if (capacity > 0 && adults > capacity) {
            adults = capacity;
            adultsEl.value = String(adults);
        }

        // Children is optional — blank means 0 (e.g. adults already fill capacity).
        childrenEl.required = false;
        const remaining = capacity > 0 ? Math.max(0, capacity - adults) : null;
        let children = Math.max(0, parseInt(childrenEl.value || '0', 10) || 0);
        if (remaining !== null && children > remaining) {
            children = remaining;
            childrenEl.value = String(children);
        }

        const total = adults + children;
        totalEl.value = String(total);

        if (capacity > 0) {
            adultsEl.max = String(capacity);
            childrenEl.max = String(remaining);
            totalEl.max = String(capacity);
        }

        const over = capacity > 0 && total > capacity;
        if (errorEl) {
            errorEl.classList.toggle('d-none', !over);
            errorEl.textContent = over
                ? `Total guests (${total}) exceeds capacity of ${capacity}.`
                : 'Total guests cannot exceed capacity.';
        }
        adultsEl.classList.toggle('is-invalid', over);
        childrenEl.classList.toggle('is-invalid', over);
        totalEl.classList.toggle('is-invalid', over);

        const submitBtn = form.querySelector('[type="submit"], button:not([type]), .rp-avail-btn-secondary');
        if (submitBtn) {
            submitBtn.disabled = over;
        }

        return !over;
    };

    document.querySelectorAll('form').forEach((form) => {
        if (!form.querySelector('[data-rp-guest-adults]')) return;

        const sync = () => syncGuestCapacity(form);
        form.querySelectorAll('[data-rp-guest-adults], [data-rp-guest-children], [data-rp-capacity-select]').forEach((el) => {
            el.addEventListener('input', sync);
            el.addEventListener('change', sync);
        });
        form.addEventListener('submit', (event) => {
            if (!syncGuestCapacity(form)) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        sync();
    });

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

    const enhancePasswordFields = () => {
        document.querySelectorAll('input[type="password"]').forEach((input) => {
            if (input.dataset.rpPasswordToggle === '1') return;
            input.dataset.rpPasswordToggle = '1';

            let wrap = input.closest('.input-group');
            let button = wrap?.querySelector('[data-rp-toggle-password], #togglePassword');

            if (!wrap) {
                wrap = document.createElement('div');
                wrap.className = 'input-group';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);
            }

            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-secondary';
                button.setAttribute('data-rp-toggle-password', '');
                button.setAttribute('aria-label', 'Show password');
                button.innerHTML = '<i class="bi bi-eye" aria-hidden="true"></i>';
                wrap.appendChild(button);
            } else {
                button.setAttribute('data-rp-toggle-password', '');
            }

            const icon = button.querySelector('i') || button;

            button.addEventListener('click', () => {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                if (icon.classList) {
                    icon.classList.toggle('bi-eye', !show);
                    icon.classList.toggle('bi-eye-slash', show);
                }
            });
        });
    };

    enhancePasswordFields();

    document.querySelectorAll('[data-rp-promo-input]').forEach((input) => {
        const wrap = input.closest('form') || document;
        const button = wrap.querySelector('[data-rp-promo-check]');
        const feedback = wrap.querySelector('[data-rp-promo-feedback]');
        const estimate = wrap.querySelector('[data-rp-promo-estimate]');
        const totalEl = wrap.querySelector('[data-calc-total]');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        const nightsFromForm = () => {
            const checkIn = wrap.querySelector('[data-calc-check-in], [name="check_in_date"]')?.value;
            const checkOut = wrap.querySelector('[data-calc-check-out], [name="check_out_date"]')?.value;
            if (!checkIn || !checkOut) return 1;
            const start = new Date(`${checkIn}T00:00:00`);
            const end = new Date(`${checkOut}T00:00:00`);
            const days = Math.round((end - start) / 86400000);
            return Math.max(1, days);
        };

        const applyCheck = async () => {
            const code = (input.value || '').trim();
            if (!code) {
                if (feedback) feedback.textContent = 'Leave blank if you don’t have a promo.';
                if (estimate) estimate.textContent = '';
                return;
            }

            try {
                const response = await fetch(input.dataset.rpPromoValidateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf || '',
                    },
                    body: JSON.stringify({
                        promo_code: code,
                        accommodation_id: Number(input.dataset.rpPromoAccommodation || 0),
                        rate: Number(input.dataset.rpPromoRate || totalEl?.dataset.calcRate || 0),
                        nights: nightsFromForm(),
                    }),
                });
                const data = await response.json();
                if (!response.ok) {
                    const message = data?.errors?.promo_code?.[0] || data?.message || 'Invalid promo code.';
                    if (feedback) feedback.textContent = message;
                    if (estimate) estimate.textContent = '';
                    input.classList.add('is-invalid');
                    return;
                }

                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
                input.value = data.code;
                if (feedback) feedback.textContent = data.message;
                if (estimate) {
                    estimate.textContent = `Promo total: ₱${Number(data.promo_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (save ₱${Number(data.discount_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })})`;
                }
                if (totalEl && data.promo_total != null) {
                    totalEl.textContent = `₱${Number(data.promo_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            } catch (e) {
                if (feedback) feedback.textContent = 'Could not validate promo code right now.';
            }
        };

        button?.addEventListener('click', (event) => {
            event.preventDefault();
            applyCheck();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                applyCheck();
            }
        });
    });

    document.querySelectorAll('[data-rp-register-form]').forEach((form) => {
        const field = (name) => form.querySelector(`[data-rp-register-field="${name}"]`);
        const feedback = (name) => form.querySelector(`[data-rp-register-feedback="${name}"]`);
        const hint = (name) => form.querySelector(`[data-rp-register-hint="${name}"]`);
        const touched = new Set();

        const normalizePhone = (value) => String(value || '').replace(/[\s\-()]/g, '');

        const validators = {
            name: (value) => {
                const trimmed = value.trim();
                if (!trimmed) return 'Please enter your full name.';
                if (trimmed.length < 2) return 'Full name must be at least 2 characters.';
                if (!/^[\p{L}]+(?:[ '\-.][\p{L}]+)*$/u.test(trimmed)) {
                    return 'Full name may only include letters, spaces, hyphens, and apostrophes.';
                }
                return '';
            },
            email: (value) => {
                const trimmed = value.trim();
                if (!trimmed) return 'Please enter your email address.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(trimmed)) {
                    return 'Enter a valid email address (e.g. you@email.com).';
                }
                return '';
            },
            phone: (value) => {
                const normalized = normalizePhone(value);
                if (!normalized) return 'Please enter your contact number.';
                if (!/^(?:\+?63|0)9\d{9}$/.test(normalized)) {
                    return 'Enter a valid PH mobile number, e.g. 09171234567.';
                }
                return '';
            },
            address: (value) => {
                if (value.trim().length > 1000) return 'Address must not exceed 1000 characters.';
                return '';
            },
            password: (value) => {
                if (!value) return 'Please create a password.';
                if (value.length < 8) return 'Password must be at least 8 characters.';
                if (!/[A-Z]/.test(value) || !/[a-z]/.test(value) || !/\d/.test(value)) {
                    return 'Use uppercase, lowercase, and a number.';
                }
                return '';
            },
            password_confirmation: (value) => {
                const password = field('password')?.value || '';
                if (!value) return 'Please confirm your password.';
                if (value !== password) return 'Passwords do not match.';
                return '';
            },
        };

        const updatePasswordChecks = () => {
            const value = field('password')?.value || '';
            const rules = {
                length: value.length >= 8,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /\d/.test(value),
            };
            Object.entries(rules).forEach(([rule, met]) => {
                const item = form.querySelector(`[data-rp-pw-rule="${rule}"]`);
                if (!item) return;
                item.classList.toggle('is-met', met);
                const icon = item.querySelector('i');
                if (icon) {
                    icon.className = met ? 'bi bi-check-circle-fill' : 'bi bi-circle';
                }
            });
        };

        const setFieldState = (name, message, { force = false } = {}) => {
            const input = field(name);
            const messageEl = feedback(name);
            const hintEl = hint(name);
            if (!input || !messageEl) return !message;

            const showError = Boolean(message) && (force || touched.has(name) || input.classList.contains('is-invalid'));
            const showValid = !message && name !== 'address' && (force || touched.has(name));

            input.classList.toggle('is-invalid', showError);
            input.classList.toggle('is-valid', showValid);

            if (showError) {
                messageEl.textContent = message;
                messageEl.classList.add('d-block');
                hintEl?.classList.add('d-none');
            } else {
                messageEl.classList.remove('d-block');
                hintEl?.classList.remove('d-none');
            }

            return !message;
        };

        const validateField = (name, options = {}) => {
            const input = field(name);
            if (!input || !validators[name]) return true;
            return setFieldState(name, validators[name](input.value), options);
        };

        const validateAll = (options = {}) => {
            updatePasswordChecks();
            return ['name', 'email', 'phone', 'address', 'password', 'password_confirmation']
                .map((name) => validateField(name, options))
                .every(Boolean);
        };

        ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => {
            const input = field(name);
            if (!input) return;

            input.addEventListener('input', () => {
                if (name === 'password') {
                    updatePasswordChecks();
                    if (touched.has('password_confirmation') || field('password_confirmation')?.value) {
                        validateField('password_confirmation');
                    }
                }
                if (touched.has(name) || input.classList.contains('is-invalid')) {
                    validateField(name);
                } else if (name === 'password') {
                    updatePasswordChecks();
                }
            });

            input.addEventListener('blur', () => {
                touched.add(name);
                validateField(name, { force: true });
            });
        });

        form.addEventListener('submit', (event) => {
            ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => touched.add(name));
            if (!validateAll({ force: true })) {
                event.preventDefault();
                const firstInvalid = form.querySelector('.is-invalid');
                firstInvalid?.focus();
            }
        });

        updatePasswordChecks();
        if (form.querySelector('.is-invalid')) {
            ['name', 'email', 'phone', 'address', 'password', 'password_confirmation'].forEach((name) => {
                const input = field(name);
                if (input?.classList.contains('is-invalid')) {
                    touched.add(name);
                    validateField(name, { force: true });
                }
            });
        }
    });

    document.querySelectorAll('[data-demo-email]').forEach((button) => {
        button.addEventListener('click', () => {
            const scope = button.closest('.rp-login-modal-body, .rp-auth-card') || document;
            const emailInput = scope.querySelector('input[type="email"]') || document.getElementById('email');
            const demoPasswordInput = scope.querySelector('input[type="password"]') || document.getElementById('password');
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
    const iosInstallHelp = document.getElementById('iosInstallHelp');
    const androidInstallHelp = document.getElementById('androidInstallHelp');
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;

    if (isStandalone) {
        installBtn?.classList.add('d-none');
        iosInstallHelp?.classList.add('d-none');
        androidInstallHelp?.classList.add('d-none');
    } else if (isIos) {
        iosInstallHelp?.classList.remove('d-none');
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt = event;
        installBtn?.classList.remove('d-none');
        androidInstallHelp?.classList.add('d-none');
    });

    installBtn?.addEventListener('click', async () => {
        if (deferredInstallPrompt) {
            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            installBtn.classList.add('d-none');
            return;
        }

        if (isIos) {
            iosInstallHelp?.classList.remove('d-none');
            iosInstallHelp?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            return;
        }

        androidInstallHelp?.classList.remove('d-none');
        androidInstallHelp?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    window.addEventListener('appinstalled', () => {
        deferredInstallPrompt = null;
        installBtn?.classList.add('d-none');
        iosInstallHelp?.classList.add('d-none');
        androidInstallHelp?.classList.add('d-none');
    });

    initAvailabilityCalendar();
    initPromoDatetimePicker();
});

function initPromoDatetimePicker() {
    const modalEl = document.getElementById('rpPromoDatetimeModal');
    const fields = Array.from(document.querySelectorAll('[data-rp-promo-dt-field]'));
    if (!modalEl || !fields.length || !window.bootstrap) return;

    const titleEl = modalEl.querySelector('#rpPromoDatetimeModalLabel');
    const monthTitleEl = modalEl.querySelector('[data-rp-promo-dt-title]');
    const daysEl = modalEl.querySelector('[data-rp-promo-dt-days]');
    const hourEl = modalEl.querySelector('[data-rp-promo-dt-hour]');
    const minuteEl = modalEl.querySelector('[data-rp-promo-dt-minute]');
    const ampmEl = modalEl.querySelector('[data-rp-promo-dt-ampm]');
    const previewEl = modalEl.querySelector('[data-rp-promo-dt-preview]');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    let activeField = null;
    let viewYear = new Date().getFullYear();
    let viewMonth = new Date().getMonth() + 1;
    let selectedDate = null;
    let selectedHour12 = 12;
    let selectedMinute = 0;
    let selectedAmPm = 'AM';

    const pad = (n) => String(n).padStart(2, '0');
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const toLocalValue = (date) => (
        `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
    );

    const formatDisplay = (value) => {
        if (!value) return 'Select date & time';
        const date = new Date(`${value}:00`);
        if (Number.isNaN(date.getTime())) return 'Select date & time';
        const h24 = date.getHours();
        const h12 = h24 % 12 || 12;
        const ampm = h24 >= 12 ? 'PM' : 'AM';
        return `${pad(date.getMonth() + 1)}/${pad(date.getDate())}/${date.getFullYear()} ${h12}:${pad(date.getMinutes())} ${ampm}`;
    };

    const parseValue = (value) => {
        if (!value) return null;
        const date = new Date(`${value}:00`);
        return Number.isNaN(date.getTime()) ? null : date;
    };

    const getMinDate = () => {
        const now = new Date();
        now.setSeconds(0, 0);
        if (activeField?.dataset.rpPromoDtRole === 'end') {
            const startValue = document.querySelector('[data-rp-promo-dt-role="start"] [data-rp-promo-dt-value]')?.value;
            const startDate = parseValue(startValue);
            if (startDate && startDate > now) return startDate;
        }
        if (activeField?.dataset.rpPromoDtRole === 'start' && activeField.dataset.rpAllowPastStart === '1') {
            const existing = parseValue(activeField.querySelector('[data-rp-promo-dt-value]')?.value);
            if (existing && existing < now) return existing;
        }
        return now;
    };

    const buildSelectedDate = () => {
        if (!selectedDate) return null;
        let hour24 = selectedHour12 % 12;
        if (selectedAmPm === 'PM') hour24 += 12;
        const date = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), hour24, selectedMinute, 0, 0);
        return date;
    };

    const isDisabledDate = (date) => {
        const min = getMinDate();
        const day = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const minDay = new Date(min.getFullYear(), min.getMonth(), min.getDate());
        return day < minDay;
    };

    const isDisabledTime = (hour12, minute, ampm) => {
        if (!selectedDate) return false;
        let hour24 = hour12 % 12;
        if (ampm === 'PM') hour24 += 12;
        const candidate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), hour24, minute, 0, 0);
        return candidate < getMinDate();
    };

    const syncPreview = () => {
        const date = buildSelectedDate();
        if (!previewEl) return;
        previewEl.textContent = date ? formatDisplay(toLocalValue(date)) : '—';
    };

    const renderDays = () => {
        if (!daysEl || !monthTitleEl) return;
        monthTitleEl.textContent = `${monthNames[viewMonth - 1]} ${viewYear}`;
        daysEl.innerHTML = '';

        const first = new Date(viewYear, viewMonth - 1, 1);
        const startPad = first.getDay();
        const daysInMonth = new Date(viewYear, viewMonth, 0).getDate();

        for (let i = 0; i < startPad; i += 1) {
            const empty = document.createElement('span');
            empty.className = 'rp-promo-dt-day is-empty';
            daysEl.appendChild(empty);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(viewYear, viewMonth - 1, day);
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-promo-dt-day';
            btn.textContent = String(day);
            const disabled = isDisabledDate(date);
            if (disabled) {
                btn.classList.add('is-disabled');
                btn.disabled = true;
            }
            if (
                selectedDate
                && selectedDate.getFullYear() === date.getFullYear()
                && selectedDate.getMonth() === date.getMonth()
                && selectedDate.getDate() === date.getDate()
            ) {
                btn.classList.add('is-selected');
            }
            btn.addEventListener('click', () => {
                selectedDate = date;
                // If current time is now in the past for this date, bump to min.
                const built = buildSelectedDate();
                const min = getMinDate();
                if (!built || built < min) {
                    selectedHour12 = min.getHours() % 12 || 12;
                    selectedMinute = min.getMinutes();
                    selectedAmPm = min.getHours() >= 12 ? 'PM' : 'AM';
                }
                renderDays();
                renderTime();
                syncPreview();
            });
            daysEl.appendChild(btn);
        }
    };

    const renderTimeList = (container, items, selected, onPick, disabledCheck) => {
        if (!container) return;
        container.innerHTML = '';
        items.forEach((item) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'rp-promo-dt-time-option';
            btn.textContent = item.label;
            if (item.value === selected) btn.classList.add('is-selected');
            if (disabledCheck?.(item.value)) {
                btn.classList.add('is-disabled');
                btn.disabled = true;
            }
            btn.addEventListener('click', () => {
                onPick(item.value);
                renderTime();
                syncPreview();
            });
            container.appendChild(btn);
        });
        const selectedBtn = container.querySelector('.is-selected');
        selectedBtn?.scrollIntoView({ block: 'nearest' });
    };

    const renderTime = () => {
        const hours = Array.from({ length: 12 }, (_, i) => {
            const value = i + 1;
            return { value, label: pad(value) };
        });
        const minutes = Array.from({ length: 60 }, (_, i) => ({ value: i, label: pad(i) }));
        renderTimeList(hourEl, hours, selectedHour12, (v) => { selectedHour12 = v; }, (v) => isDisabledTime(v, selectedMinute, selectedAmPm));
        renderTimeList(minuteEl, minutes, selectedMinute, (v) => { selectedMinute = v; }, (v) => isDisabledTime(selectedHour12, v, selectedAmPm));
        renderTimeList(ampmEl, [{ value: 'AM', label: 'AM' }, { value: 'PM', label: 'PM' }], selectedAmPm, (v) => { selectedAmPm = v; }, (v) => isDisabledTime(selectedHour12, selectedMinute, v));
    };

    const openForField = (field) => {
        activeField = field;
        const valueInput = field.querySelector('[data-rp-promo-dt-value]');
        const current = parseValue(valueInput?.value) || new Date();
        const min = getMinDate();
        const seed = current < min && field.dataset.rpAllowPastStart !== '1' ? min : current;

        selectedDate = new Date(seed.getFullYear(), seed.getMonth(), seed.getDate());
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth() + 1;
        selectedHour12 = seed.getHours() % 12 || 12;
        selectedMinute = seed.getMinutes();
        selectedAmPm = seed.getHours() >= 12 ? 'PM' : 'AM';

        if (titleEl) {
            titleEl.textContent = field.dataset.rpPromoDtRole === 'end'
                ? 'Select end date & time'
                : 'Select start date & time';
        }

        renderDays();
        renderTime();
        syncPreview();
        modal.show();
    };

    fields.forEach((field) => {
        field.querySelector('[data-rp-promo-dt-open]')?.addEventListener('click', () => openForField(field));
    });

    modalEl.querySelector('[data-rp-promo-dt-prev]')?.addEventListener('click', () => {
        viewMonth -= 1;
        if (viewMonth < 1) {
            viewMonth = 12;
            viewYear -= 1;
        }
        renderDays();
    });

    modalEl.querySelector('[data-rp-promo-dt-next]')?.addEventListener('click', () => {
        viewMonth += 1;
        if (viewMonth > 12) {
            viewMonth = 1;
            viewYear += 1;
        }
        renderDays();
    });

    modalEl.querySelector('[data-rp-promo-dt-clear]')?.addEventListener('click', () => {
        if (!activeField) return;
        const valueInput = activeField.querySelector('[data-rp-promo-dt-value]');
        const label = activeField.querySelector('[data-rp-promo-dt-label]');
        if (valueInput) valueInput.value = '';
        if (label) {
            label.textContent = activeField.dataset.rpPromoDtRole === 'end'
                ? 'Select end date & time'
                : 'Select start date & time';
        }
        modal.hide();
    });

    modalEl.querySelector('[data-rp-promo-dt-today]')?.addEventListener('click', () => {
        const min = getMinDate();
        selectedDate = new Date(min.getFullYear(), min.getMonth(), min.getDate());
        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth() + 1;
        selectedHour12 = min.getHours() % 12 || 12;
        selectedMinute = min.getMinutes();
        selectedAmPm = min.getHours() >= 12 ? 'PM' : 'AM';
        renderDays();
        renderTime();
        syncPreview();
    });

    modalEl.querySelector('[data-rp-promo-dt-ok]')?.addEventListener('click', () => {
        if (!activeField || !selectedDate) return;
        let date = buildSelectedDate();
        const min = getMinDate();
        if (!date || date < min) {
            date = min;
        }
        const value = toLocalValue(date);
        const valueInput = activeField.querySelector('[data-rp-promo-dt-value]');
        const label = activeField.querySelector('[data-rp-promo-dt-label]');
        if (valueInput) valueInput.value = value;
        if (label) label.textContent = formatDisplay(value);
        modal.hide();
    });
}

function initAvailabilityCalendar() {
    const modalEl = document.getElementById('rpAvailabilityModal');
    const form = document.querySelector('[data-rp-availability-form]');
    const calendarRoot = document.querySelector('[data-rp-availability-calendar]');

    if (!modalEl || !form || !calendarRoot || !window.bootstrap) {
        return;
    }

    const occupiedUrl = form.dataset.occupiedUrl;
    const accommodationSelect = form.querySelector('[data-rp-capacity-select]');
    const checkInInput = form.querySelector('[data-stay-check-in]');
    const checkOutInput = form.querySelector('[data-stay-check-out]');
    const checkInDisplay = form.querySelector('[data-rp-date-display="check_in"]');
    const checkOutDisplay = form.querySelector('[data-rp-date-display="check_out"]');
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
    const formatDisplay = (ymd) => (ymd ? parseYmd(ymd).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '');
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
        const selectedAccommodation = accommodationSelect?.value || '';
        const resolvedOccupiedUrl = occupiedUrl.replace('__ACCOMMODATION__', encodeURIComponent(selectedAccommodation));
        const url = new URL(resolvedOccupiedUrl, window.location.origin);
        url.searchParams.set('year', String(year));
        url.searchParams.set('month', String(month));
        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
            throw new Error('Could not load availability');
        }
        return response.json();
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
        if (!occupiedUrl) {
            renderCalendar();
            return;
        }
        daysEl.classList.add('is-loading');
        try {
            const data = await fetchOccupied(viewYear, viewMonth);
            const occupied = data?.occupied || [];
            const unpaid = data?.unpaid_deposit || [];
            [...occupied, ...unpaid].forEach((date) => occupiedSet.add(date));
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

    accommodationSelect?.addEventListener('change', () => {
        occupiedSet = new Set();
    });

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
        if (checkInDisplay) checkInDisplay.value = formatDisplay(pendingCheckIn);
        if (checkOutDisplay) checkOutDisplay.value = formatDisplay(pendingCheckOut);
        checkInInput.dispatchEvent(new Event('change', { bubbles: true }));
        modal.hide();
        if (!form.hasAttribute('data-rp-no-auto-submit')) {
            form.submit();
        }
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
